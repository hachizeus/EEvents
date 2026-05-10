<?php

namespace HiEvents\Http\Actions\Orders\Payment\Paystack;

use HiEvents\Exceptions\Paystack\PaystackTransactionInitializationException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Application\Handlers\Order\Payment\Paystack\InitializePaystackTransactionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InitializePaystackTransactionActionPublic extends BaseAction
{
    public function __construct(
        private readonly InitializePaystackTransactionHandler $handler,
    ) {
    }

    public function __invoke(int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $result = $this->handler->handle($orderShortId);
        } catch (PaystackTransactionInitializationException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->jsonResponse($result->toArray());
    }
}
