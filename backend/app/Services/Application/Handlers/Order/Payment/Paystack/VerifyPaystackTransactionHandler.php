<?php

namespace HiEvents\Services\Application\Handlers\Order\Payment\Paystack;

use HiEvents\DomainObjects\Generated\PaystackPaymentDomainObjectAbstract;
use HiEvents\Repository\Interfaces\AccountRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Payment\Paystack\PaystackTransactionService;
use Psr\Log\LoggerInterface;

readonly class VerifyPaystackTransactionHandler
{
    public function __construct(
        private OrderRepositoryInterface            $orderRepository,
        private AccountRepositoryInterface          $accountRepository,
        private PaystackPaymentsRepositoryInterface $paystackPaymentsRepository,
        private PaystackTransactionService          $paystackTransactionService,
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

        // Get the account ID to use the right API keys
        $account = $this->accountRepository->findByEventId($eventId);

        $transactionData = $this->paystackTransactionService->verifyTransaction(
            $paystackPayment->getReference(),
            $account?->getId()
        );

        if (($transactionData['status'] ?? '') === 'success') {
            return ['status' => 'succeeded'];
        }

        return ['status' => $transactionData['status'] ?? 'pending'];
    }
}
