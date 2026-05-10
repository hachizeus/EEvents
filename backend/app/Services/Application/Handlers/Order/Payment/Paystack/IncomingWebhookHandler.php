<?php

namespace HiEvents\Services\Application\Handlers\Order\Payment\Paystack;

use HiEvents\DomainObjects\Enums\PaymentProviders;
use HiEvents\DomainObjects\Generated\OrderDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\PaystackPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\OrderItemDomainObject;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\DomainObjects\Status\OrderPaymentStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Events\OrderStatusChangedEvent;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSettingsRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Product\ProductQuantityUpdateService;
use HiEvents\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use HiEvents\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use HiEvents\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use HiEvents\Services\Infrastructure\Paystack\PaystackConfigurationService;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

class IncomingWebhookHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface            $orderRepository,
        private readonly PaystackPaymentsRepositoryInterface $paystackPaymentsRepository,
        private readonly AttendeeRepositoryInterface         $attendeeRepository,
        private readonly ProductQuantityUpdateService        $quantityUpdateService,
        private readonly DomainEventDispatcherService        $domainEventDispatcherService,
        private readonly EventSettingsRepositoryInterface    $eventSettingsRepository,
        private readonly DatabaseManager                     $databaseManager,
        private readonly LoggerInterface                     $logger,
        private readonly PaystackConfigurationService        $paystackConfigurationService,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(string $payload, string $signature): void
    {
        $event = json_decode($payload, true);

        if (!$event) {
            $this->logger->warning('Invalid Paystack webhook payload');
            return;
        }

        // Try to find the order reference to look up the account's webhook secret
        $reference = $event['data']['reference'] ?? null;
        $webhookSecret = $this->resolveWebhookSecret($reference);

        // Verify webhook signature
        $computedSignature = hash_hmac('sha512', $payload, $webhookSecret);

        if (!hash_equals($computedSignature, $signature)) {
            $this->logger->warning('Paystack webhook signature verification failed');
            return;
        }

        $eventType = $event['event'] ?? '';

        $this->logger->info('Paystack webhook received', ['event' => $eventType]);

        match ($eventType) {
            'charge.success' => $this->handleChargeSuccess($event['data'] ?? []),
            'transfer.success' => $this->handleTransferSuccess($event['data'] ?? []),
            default => $this->logger->debug('Unhandled Paystack event type', ['event' => $eventType]),
        };
    }

    private function resolveWebhookSecret(?string $reference): string
    {
        // Paystack sends all webhooks to a single URL configured in the dashboard.
        // The webhook secret is the system-level secret set in the environment.
        // Per-account keys are only used for initiating/verifying transactions.
        return $this->paystackConfigurationService->getWebhookSecret() ?? '';
    }

    /**
     * @throws Throwable
     */
    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            $this->logger->error('Paystack charge.success event missing reference');
            return;
        }

        $this->databaseManager->transaction(function () use ($data, $reference) {
            $paystackPayment = $this->paystackPaymentsRepository->findFirstWhere([
                PaystackPaymentDomainObjectAbstract::REFERENCE => $reference,
            ]);

            if (!$paystackPayment) {
                $this->logger->error('Paystack payment not found for reference', ['reference' => $reference]);
                return;
            }

            // Check if already processed
            if ($paystackPayment->getStatus() === 'success') {
                $this->logger->info('Paystack payment already processed', ['reference' => $reference]);
                return;
            }

            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($paystackPayment->getOrderId());

            if (!$order) {
                $this->logger->error('Order not found for Paystack payment', ['order_id' => $paystackPayment->getOrderId()]);
                return;
            }

            // Update paystack payment record
            $this->paystackPaymentsRepository->updateWhere(
                attributes: [
                    PaystackPaymentDomainObjectAbstract::STATUS => 'success',
                    PaystackPaymentDomainObjectAbstract::TRANSACTION_ID => (string) ($data['id'] ?? ''),
                    PaystackPaymentDomainObjectAbstract::GATEWAY_RESPONSE => $data['gateway_response'] ?? null,
                    PaystackPaymentDomainObjectAbstract::PAID_AT => $data['paid_at'] ?? null,
                    PaystackPaymentDomainObjectAbstract::AMOUNT => $data['amount'] ?? null,
                    PaystackPaymentDomainObjectAbstract::CURRENCY => strtoupper($data['currency'] ?? ''),
                ],
                where: [PaystackPaymentDomainObjectAbstract::REFERENCE => $reference]
            );

            // Update order status
            $updatedOrder = $this->orderRepository->updateFromArray($order->getId(), [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_RECEIVED->name,
                OrderDomainObjectAbstract::STATUS => OrderStatus::COMPLETED->name,
                OrderDomainObjectAbstract::PAYMENT_PROVIDER => PaymentProviders::PAYSTACK->value,
            ]);

            // Update attendee statuses
            $this->attendeeRepository->updateWhere(
                attributes: ['status' => AttendeeStatus::ACTIVE->name],
                where: ['order_id' => $order->getId(), 'status' => AttendeeStatus::AWAITING_PAYMENT->name],
            );

            // Update product quantities
            $this->quantityUpdateService->updateQuantitiesFromOrder($updatedOrder);

            // Get event settings for invoice creation
            $eventSettings = $this->eventSettingsRepository->findFirstWhere(['event_id' => $order->getEventId()]);

            event(new OrderStatusChangedEvent($updatedOrder, createInvoice: $eventSettings?->getEnableInvoicing() ?? false));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_CREATED,
                    orderId: $updatedOrder->getId()
                ),
            );

            $this->logger->info('Paystack charge.success processed', [
                'order_id' => $order->getId(),
                'reference' => $reference,
            ]);
        });
    }

    private function handleTransferSuccess(array $data): void
    {
        $this->logger->info('Paystack transfer.success received', ['data' => $data]);
    }
}
