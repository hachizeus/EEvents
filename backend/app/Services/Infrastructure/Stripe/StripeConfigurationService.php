<?php

namespace HiEvents\Services\Infrastructure\Paystack;

class PaystackConfigurationService
{
    public function getSecretKey(): ?string
    {
        return config('services.paystack.secret_key');
    }

    public function getPublicKey(): ?string
    {
        return config('services.paystack.public_key');
    }

    public function getWebhookSecret(): ?string
    {
        return config('services.paystack.webhook_secret');
    }
}

// This file is deprecated as Paystack is now the primary payment provider.
