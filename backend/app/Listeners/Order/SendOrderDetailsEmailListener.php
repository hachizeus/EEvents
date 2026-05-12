<?php

namespace HiEvents\Listeners\Order;

use HiEvents\Events\OrderStatusChangedEvent;
use HiEvents\Jobs\Order\SendOrderDetailsEmailJob;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderDetailsEmailListener
{
    public function handle(OrderStatusChangedEvent $changedEvent): void
    {
        if (!$changedEvent->sendEmails) {
            return;
        }

        try {
            dispatch(new SendOrderDetailsEmailJob($changedEvent->order));
        } catch (Throwable $e) {
            Log::error('Failed to dispatch SendOrderDetailsEmailJob', [
                'order_id' => $changedEvent->order->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
