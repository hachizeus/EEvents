<?php

namespace HiEvents\Http\Actions\Orders\Payment;

use HiEvents\DomainObjects\Enums\PaymentProviders;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Exceptions\RefundNotPossibleException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Order\RefundOrderRequest;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Resources\Order\OrderResource;
use HiEvents\Services\Application\Handlers\Order\DTO\RefundOrderDTO;
use HiEvents\Services\Application\Handlers\Order\Payment\Paystack\RefundPaystackOrderHandler;
use HiEvents\Services\Application\Handlers\Order\Payment\Stripe\RefundOrderHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Throwable;

class RefundOrderAction extends BaseAction
{
    public function __construct(
        private readonly RefundOrderHandler         $stripeRefundOrderHandler,
        private readonly RefundPaystackOrderHandler $paystackRefundOrderHandler,
        private readonly OrderRepositoryInterface   $orderRepository,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function __invoke(RefundOrderRequest $request, int $eventId, int $orderId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->orderRepository->findFirstWhere([
            'event_id' => $eventId,
            'id' => $orderId,
        ]);

        if (!$order) {
            return $this->notFoundResponse();
        }

        $refundDTO = RefundOrderDTO::fromArray(array_merge($request->validated(), [
            'event_id' => $eventId,
            'order_id' => $orderId,
        ]));

        try {
            // Route to the correct refund handler based on payment provider
            if ($order->getPaymentProvider() === PaymentProviders::PAYSTACK->value) {
                $updatedOrder = $this->paystackRefundOrderHandler->handle($refundDTO);
            } else {
                // Default to Stripe handler for legacy orders
                $updatedOrder = $this->stripeRefundOrderHandler->handle($refundDTO);
            }
        } catch (ApiErrorException $exception) {
            throw ValidationException::withMessages([
                'amount' => 'Stripe error: ' . $exception->getMessage(),
            ]);
        } catch (RefundNotPossibleException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        return $this->resourceResponse(OrderResource::class, $updatedOrder);
    }
}
