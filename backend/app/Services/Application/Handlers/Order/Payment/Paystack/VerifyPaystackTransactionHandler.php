<?php

namespace HiEvents\Services\Application\Handlers\Order\Payment\Paystack;

use HiEvents\DomainObjects\Enums\PaymentProviders;
use HiEvents\DomainObjects\Generated\OrderDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\PaystackPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\Status\OrderPaymentStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\Repository\Interfaces\AccountRepositoryInterface;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSettingsRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Payment\Paystack\PaystackTransactionService;
use HiEvents\Services\Domain\Product\ProductQuantityUpdateService;
use HiEvents\Events\OrderStatusChangedEvent;
use HiEvents\DomainObjects\OrderItemDomainObject;
use Psr\Log\LoggerInterface;

readonly class VerifyPaystackTransactionHandler
{
    public function __construct(
        private OrderRepositoryInterface            $orderRepository,
        private AccountRepositoryInterface          $accountRepository,
        private AttendeeRepositoryInterface         $attendeeRepository,
        private PaystackPaymentsRepositoryInterface $paystackPaymentsRepository,
        private PaystackTransactionService          $paystackTransactionService,
        private ProductQuantityUpdateService        $quantityUpdateService,
        private EventSettingsRepositoryInterface    $eventSettingsRepository,
        private LoggerInterface                     $logger,
    ) {
    }

    public function handle(int $eventId, string $orderShortId): array
    {
        $order = $this->orderRepository->findByShortId($orderShortId);

        if (!$order || $order->getEventId() !== $eventId) {
            return ['status' => 'not_found'];
        }

        $paystackPayment = $this->paystackPaymentsRepository->findFirstWhere([
            PaystackPaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
        ]);

        if (!$paystackPayment) {
            return ['status' => 'not_found'];
        }

        // Already succeeded — return immediately without hitting Paystack API
        if ($paystackPayment->getStatus() === 'success') {
            return ['status' => 'succeeded'];
        }

        // Order already completed (e.g. webhook already processed it)
        if ($order->getStatus() === OrderStatus::COMPLETED->name) {
            return ['status' => 'succeeded'];
        }

        // Get the account ID to use the right API keys
        $account = $this->accountRepository->findByEventId($eventId);

        $transactionData = $this->paystackTransactionService->verifyTransaction(
            $paystackPayment->getReference(),
            $account?->getId()
        );

        if (($transactionData['status'] ?? '') === 'success') {
            // Update paystack payment record
            $this->paystackPaymentsRepository->updateWhere(
                attributes: [
                    PaystackPaymentDomainObjectAbstract::STATUS => 'success',
                    PaystackPaymentDomainObjectAbstract::TRANSACTION_ID => (string) ($transactionData['id'] ?? ''),
                    PaystackPaymentDomainObjectAbstract::GATEWAY_RESPONSE => $transactionData['gateway_response'] ?? null,
                    PaystackPaymentDomainObjectAbstract::PAID_AT => $transactionData['paid_at'] ?? null,
                    PaystackPaymentDomainObjectAbstract::AMOUNT => $transactionData['amount'] ?? null,
                    PaystackPaymentDomainObjectAbstract::CURRENCY => strtoupper($transactionData['currency'] ?? ''),
                ],
                where: [PaystackPaymentDomainObjectAbstract::ID => $paystackPayment->getId()]
            );

            // Reload order with items
            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($order->getId());

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

            // Fire order completed event (sends confirmation email etc.)
            $eventSettings = $this->eventSettingsRepository->findFirstWhere(['event_id' => $order->getEventId()]);
            event(new OrderStatusChangedEvent($updatedOrder, createInvoice: $eventSettings?->getEnableInvoicing() ?? false));

            $this->logger->info('Paystack payment verified and order completed via manual verification', [
                'order_id' => $order->getId(),
                'reference' => $paystackPayment->getReference(),
            ]);

            return ['status' => 'succeeded'];
        }

        return ['status' => $transactionData['status'] ?? 'pending'];
    }
}
