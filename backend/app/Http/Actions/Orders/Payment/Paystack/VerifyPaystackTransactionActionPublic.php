<?php

namespace HiEvents\Http\Actions\Orders\Payment\Paystack;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Application\Handlers\Order\Payment\Paystack\VerifyPaystackTransactionHandler;
use Illuminate\Http\JsonResponse;

class VerifyPaystackTransactionActionPublic extends BaseAction
{
    public function __construct(
        private readonly VerifyPaystackTransactionHandler $handler,
    ) {
    }

    public function __invoke(int $eventId, string $orderShortId): JsonResponse
    {
        $result = $this->handler->handle($eventId, $orderShortId);
        return $this->jsonResponse($result);
    }
}
