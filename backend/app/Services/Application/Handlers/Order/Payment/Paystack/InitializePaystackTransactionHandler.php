<?php

namespace HiEvents\Services\Application\Handlers\Order\Payment\Paystack;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrderItemDomainObject;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Exceptions\Paystack\PaystackTransactionInitializationException;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Exceptions\UnauthorizedException;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AccountRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Services\Domain\Payment\Paystack\DTOs\InitializePaystackTransactionDTO;
use HiEvents\Services\Domain\Payment\Paystack\PaystackTransactionService;
use HiEvents\Services\Infrastructure\Session\CheckoutSessionManagementService;

readonly class InitializePaystackTransactionHandler
{
    public function __construct(
        private OrderRepositoryInterface         $orderRepository,
        private AccountRepositoryInterface       $accountRepository,
        private PaystackTransactionService       $paystackTransactionService,
        private CheckoutSessionManagementService $sessionIdentifierService,
    ) {
    }

    /**
     * @throws PaystackTransactionInitializationException
     * @throws UnauthorizedException
     * @throws ResourceConflictException
     */
    public function handle(string $orderShortId): InitializePaystackTransactionDTO
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(OrderItemDomainObject::class))
            ->loadRelation(new Relationship(EventDomainObject::class, name: 'event'))
            ->findByShortId($orderShortId);

        if (!$order || !$this->sessionIdentifierService->verifySession($order->getSessionId())) {
            throw new UnauthorizedException(__('Sorry, we could not verify your session. Please create a new order.'));
        }

        if ($order->getStatus() !== OrderStatus::RESERVED->name || $order->isReservedOrderExpired()) {
            throw new ResourceConflictException(__('Sorry, this order is expired or not in a valid state.'));
        }

        // Get the account ID for this event so we can use the account's Paystack keys
        $account = $this->accountRepository->findByEventId($order->getEventId());

        return $this->paystackTransactionService->initializeTransaction($order, $account?->getId());
    }
}
