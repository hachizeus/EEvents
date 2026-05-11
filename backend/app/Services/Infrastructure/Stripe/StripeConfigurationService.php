<?php

namespace HiEvents\Services\Infrastructure\Stripe;

use HiEvents\DomainObjects\Enums\StripePlatform;

/**
 * @deprecated Stripe is no longer the primary payment provider. Use PaystackConfigurationService instead.
 * This class is kept for backward compatibility with existing Stripe payments/refunds.
 */
class StripeConfigurationService
{
    public function getSecretKey(?StripePlatform $platform = null): ?string
    {
        return config('services.stripe.secret_key');
    }

    public function getPublicKey(?StripePlatform $platform = null): ?string
    {
        return config('services.stripe.public_key');
    }

    public function getWebhookSecret(?StripePlatform $platform = null): ?string
    {
        return config('services.stripe.webhook_secret');
    }

    public function getAllWebhookSecrets(): array
    {
        return array_filter([
            'default' => config('services.stripe.webhook_secret'),
        ]);
    }

    public function getPrimaryPlatform(): ?StripePlatform
    {
        return null;
    }
}
