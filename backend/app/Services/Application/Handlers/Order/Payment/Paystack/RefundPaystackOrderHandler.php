<?php

namespace HiEvents\Services\Application\Handlers\Order\Payment\Paystack;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\Generated\OrderDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\PaystackPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\DomainObjects\PaystackPaymentDomainObject;
use HiEvents\DomainObjects\Status\OrderRefundStatus;
use HiEvents\Exceptions\RefundNotPossibleException;
use HiEvents\Mail\Order\OrderRefunded;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AccountPaystackSettingRepositoryInterface;
use HiEvents\Repository\Interfaces\AccountRepositoryInterface;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRefundRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Application\Handlers\Order\DTO\RefundOrderDTO;
use HiEvents\Services\Domain\Order\OrderCancelService;
use HiEvents\Services\Infrastructure\Paystack\PaystackClientFactory;
use HiEvents\Values\MoneyValue;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

class RefundPaystackOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface                  $orderRepository,
        private readonly EventRepositoryInterface                  $eventRepository,
        private readonly AccountRepositoryInterface                $accountRepository,
        private readonly PaystackPaymentsRepositoryInterface       $paystackPaymentsRepository,
        private readonly AccountPaystackSettingRepositoryInterface $accountPaystackSettingRepository,
        private readonly OrderRefundRepositoryInterface            $orderRefundRepository,
        private readonly Mailer                                    $mailer,
        private readonly OrderCancelService                        $orderCancelService,
        private readonly DatabaseManager                           $databaseManager,
        private readonly PaystackClientFactory                     $paystackClientFactory,
        private readonly Encrypter                                 $encrypter,
        private readonly LoggerInterface                           $logger,
    ) {
    }

    /**
     * @throws RefundNotPossibleException
     * @throws Throwable
     */
    public function handle(RefundOrderDTO $refundOrderDTO): OrderDomainObject
    {
        return $this->databaseManager->transaction(fn() => $this->refundOrder($refundOrderDTO));
    }

    private function fetchOrder(int $eventId, int $orderId): OrderDomainObject
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(PaystackPaymentDomainObject::class, name: 'paystack_payment'))
            ->findFirstWhere(['event_id' => $eventId, 'id' => $orderId]);

        if (!$order) {
            throw new ResourceNotFoundException(__('Order :id not found for event :eventId', [
                'id' => $orderId,
                'eventId' => $eventId,
            ]));
        }

        return $order;
    }

    /**
     * @throws RefundNotPossibleException
     */
    private function validateRefundability(OrderDomainObject $order): void
    {
        if (!$order->getPaystackPayment()) {
            throw new RefundNotPossibleException(__('There is no Paystack payment data associated with this order.'));
        }

        if ($order->getRefundStatus() === OrderRefundStatus::REFUND_PENDING->name) {
            throw new RefundNotPossibleException(
                __('There is already a refund pending for this order. Please wait for it to be processed.')
            );
        }
    }

    /**
     * @throws RefundNotPossibleException
     * @throws Throwable
     */
    private function refundOrder(RefundOrderDTO $refundOrderDTO): OrderDomainObject
    {
        $order = $this->fetchOrder($refundOrderDTO->event_id, $refundOrderDTO->order_id);
        $event = $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(EventSettingDomainObject::class)
            ->findById($refundOrderDTO->event_id);

        $amount = MoneyValue::fromFloat($refundOrderDTO->amount, $order->getCurrency());

        $this->validateRefundability($order);

        if ($refundOrderDTO->cancel_order) {
            $this->orderCancelService->cancelOrder($order);
        }

        // Issue refund via Paystack API
        $paystackPayment = $order->getPaystackPayment();
        $this->issuePaystackRefund($paystackPayment, $amount, $order);

        if ($refundOrderDTO->notify_buyer) {
            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send(new OrderRefunded(
                    order: $order,
                    event: $event,
                    organizer: $event->getOrganizer(),
                    eventSettings: $event->getEventSettings(),
                    refundAmount: $amount
                ));
        }

        return $this->orderRepository->updateFromArray(
            id: $order->getId(),
            attributes: [
                OrderDomainObjectAbstract::REFUND_STATUS => OrderRefundStatus::REFUND_PENDING->name,
            ]
        );
    }

    private function issuePaystackRefund(
        PaystackPaymentDomainObject $paystackPayment,
        MoneyValue                  $amount,
        OrderDomainObject           $order,
    ): void {
        try {
            // Resolve the account's secret key
            $account = $this->accountRepository->findByEventId($order->getEventId());
            $secretKey = null;

            if ($account) {
                $accountSetting = $this->accountPaystackSettingRepository->findFirstWhere([
                    \HiEvents\DomainObjects\Generated\AccountPaystackSettingDomainObjectAbstract::ACCOUNT_ID => $account->getId(),
                    \HiEvents\DomainObjects\Generated\AccountPaystackSettingDomainObjectAbstract::IS_ACTIVE => true,
                ]);

                if ($accountSetting) {
                    $secretKey = $this->encrypter->decrypt($accountSetting->getSecretKey());
                }
            }

            $client = $secretKey
                ? $this->paystackClientFactory->createWithKey($secretKey)
                : $this->paystackClientFactory->create();

            $amountInKobo = (int) round($amount->toFloat() * 100);

            $response = $client->post('refund', [
                'json' => [
                    'transaction' => $paystackPayment->getTransactionId() ?? $paystackPayment->getReference(),
                    'amount' => $amountInKobo,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!($data['status'] ?? false)) {
                throw new RefundNotPossibleException(
                    $data['message'] ?? 'Paystack refund failed'
                );
            }

            // Record the refund
            $this->orderRefundRepository->create([
                'order_id' => $order->getId(),
                'payment_provider' => 'PAYSTACK',
                'refund_id' => $data['data']['id'] ?? null,
                'amount' => $amount->toFloat(),
                'currency' => $order->getCurrency(),
                'status' => 'pending',
                'metadata' => ['transaction_id' => $paystackPayment->getTransactionId()],
            ]);

            $this->logger->info('Paystack refund initiated', [
                'order_id' => $order->getId(),
                'amount' => $amount->toFloat(),
                'reference' => $paystackPayment->getReference(),
            ]);
        } catch (RefundNotPossibleException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('Paystack refund failed', [
                'order_id' => $order->getId(),
                'error' => $e->getMessage(),
            ]);
            throw new RefundNotPossibleException('Paystack refund failed: ' . $e->getMessage());
        }
    }
}
