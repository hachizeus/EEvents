<?php

namespace HiEvents\Services\Domain\Payment\Paystack\DTOs;

class InitializePaystackTransactionDTO
{
    public function __construct(
        public readonly string $reference,
        public readonly string $accessCode,
        public readonly string $authorizationUrl,
        public readonly string $publicKey,
    ) {
    }

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'access_code' => $this->accessCode,
            'authorization_url' => $this->authorizationUrl,
            'public_key' => $this->publicKey,
        ];
    }
}
