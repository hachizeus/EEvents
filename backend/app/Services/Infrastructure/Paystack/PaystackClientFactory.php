<?php

namespace HiEvents\Services\Infrastructure\Paystack;

use GuzzleHttp\Client;

class PaystackClientFactory
{
    public function __construct(
        private readonly PaystackConfigurationService $configurationService
    ) {
    }

    public function create(): Client
    {
        return $this->createWithKey($this->configurationService->getSecretKey() ?? '');
    }

    public function createWithKey(string $secretKey): Client
    {
        return new Client([
            'base_uri' => 'https://api.paystack.co/',
            'headers' => [
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
