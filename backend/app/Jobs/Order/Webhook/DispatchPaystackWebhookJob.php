<?php

namespace HiEvents\Jobs\Order\Webhook;

use HiEvents\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;

class DispatchPaystackWebhookJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string          $transactionId,
        public DomainEventType $eventType,
    ) {
    }

    public function handle(LoggerInterface $logger): void
    {
        $logger->info('Paystack webhook job dispatched', [
            'transaction_id' => $this->transactionId,
            'event_type' => $this->eventType->name,
        ]);
    }
}
