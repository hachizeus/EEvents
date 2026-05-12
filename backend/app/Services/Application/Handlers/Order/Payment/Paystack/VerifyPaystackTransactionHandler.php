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
use HiEvents\Repository\Interfaces\AccountRepositoryInterface;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSettingsRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Payment\Paystack\PaystackTransactionService;
use HiEvents\Services\Domain\Product\ProductQuantityUpdateService;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

class VerifyPaystackTransactionHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface            $orderRepository,
        private readonly AccountRepositoryInterface          $accountRepository,
        private readonly AttendeeRepositoryInterface         $attendeeRepository,
        private readonly PaystackPaymentsRepositoryInterface $paystackPaymentsRepository,
        private readonly PaystackTransactionService          $paystackTransactionService,
        private readonly ProductQuantityUpdateService        $quantityUpdateService,
        private readonly EventSettingsRepositoryInterface    $eventSettingsRepository,
        private readonly DatabaseManager                     $databaseManager,
        private readonly LoggerInterface                     $logger,
    ) {
    }

    public function handle(int $eventId, string $orderShortId): array
    {
        $order = $this->orderRepository->findByShortId($orderShortId);

        if (!$order || $order->getEventId() !== $eventId) {
            return ['status' => 'not_found'];
        }

        // Already completed — return immediately
        if ($order->getStatus() === OrderStatus::COMPLETED->name) {
            return ['status' => 'succeeded'];
        }

        $paystackPayment = $this->paystackPaymentsRepository->findFirstWhere([
            PaystackPaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
        ]);

        if (!$paystackPayment) {
            return ['status' => 'not_found'];
        }

        // Payment already recorded as success — complete the order
        if ($paystackPayment->getStatus() === 'success') {
            return ['status' => 'succeeded'];
        }

        // Get the account to use the right API keys
        $account = $this->accountRepository->findByEventId($eventId);

        // Verify with Paystack API
        $transactionData = $this->paystackTransactionService->verifyTransaction(
            $paystackPayment->getReference(),
            $account?->getId()
        );

        if (($transactionData['status'] ?? '') !== 'success') {
            return ['status' => $transactionData['status'] ?? 'pending'];
        }

        // Payment confirmed — complete the order in a transaction
        $this->databaseManager->transaction(function () use ($paystackPayment, $order, $transactionData) {
            // Update paystack payment record
            $this->paystackPaymentsRepository->updateWhere(
                attributes: [
                    PaystackPaymentDomainObjectAbstract::STATUS => 'success',
                    PaystackPaymentDomainObjectAbstract::TRANSACTION_ID => (string)($transactionData['id'] ?? ''),
                    PaystackPaymentDomainObjectAbstract::GATEWAY_RESPONSE => $transactionData['gateway_response'] ?? null,
                    PaystackPaymentDomainObjectAbstract::PAID_AT => $transactionData['paid_at'] ?? null,
                    PaystackPaymentDomainObjectAbstract::AMOUNT => $transactionData['amount'] ?? null,
                    PaystackPaymentDomainObjectAbstract::CURRENCY => strtoupper($transactionData['currency'] ?? ''),
                ],
                where: [PaystackPaymentDomainObjectAbstract::ID => $paystackPayment->getId()]
            );

            // Reload order with items
            $orderWithItems = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($order->getId());

            // Update order status
            $updatedOrder = $this->orderRepository->updateFromArray($order->getId(), [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_RECEIVED->name,
                OrderDomainObjectAbstract::STATUS => OrderStatus::COMPLETED->name,
                OrderDomainObjectAbstract::PAYMENT_PROVIDER => PaymentProviders::PAYSTACK->value,
            ]);

            // Set updated status on the order with items so listeners have full data
            $orderWithItems->setStatus(OrderStatus::COMPLETED->name);
            $orderWithItems->setPaymentStatus(OrderPaymentStatus::PAYMENT_RECEIVED->name);

            // Activate attendees
            $this->attendeeRepository->updateWhere(
                attributes: ['status' => AttendeeStatus::ACTIVE->name],
                where: ['order_id' => $order->getId(), 'status' => AttendeeStatus::AWAITING_PAYMENT->name],
            );

            // Update product quantities
            $this->quantityUpdateService->updateQuantitiesFromOrder($orderWithItems);

            // Fire order completed event with full order (items loaded) for email/invoice/stats
            $eventSettings = $this->eventSettingsRepository->findFirstWhere(['event_id' => $order->getEventId()]);
            try {
                event(new OrderStatusChangedEvent(
                    $orderWithItems,
                    createInvoice: $eventSettings?->getEnableInvoicing() ?? false
                ));
            } catch (Throwable $e) {
                $this->logger->error('Failed to send order confirmation email after verify', [
                    'order_id' => $order->getId(),
                    'error' => $e->getMessage(),
                ]);
            }

            $this->logger->info('Paystack payment verified manually and order completed', [
                'order_id' => $order->getId(),
                'reference' => $paystackPayment->getReference(),
            ]);
        });

        return ['status' => 'succeeded'];
    }
}
