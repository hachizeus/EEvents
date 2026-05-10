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
        if ($platform === StripePlatform::CANADA) {
            return config('services.stripe_ca.secret_key');
        }
        if ($platform === StripePlatform::IRELAND) {
            return config('services.stripe_ie.secret_key');
        }
        return config('services.stripe.secret_key');
    }

    public function getPublicKey(?StripePlatform $platform = null): ?string
    {
        if ($platform === StripePlatform::CANADA) {
            return config('services.stripe_ca.public_key');
        }
        if ($platform === StripePlatform::IRELAND) {
            return config('services.stripe_ie.public_key');
        }
        return config('services.stripe.public_key');
    }

    public function getWebhookSecret(?StripePlatform $platform = null): ?string
    {
        if ($platform === StripePlatform::CANADA) {
            return config('services.stripe_ca.webhook_secret');
        }
        if ($platform === StripePlatform::IRELAND) {
            return config('services.stripe_ie.webhook_secret');
        }
        return config('services.stripe.webhook_secret');
    }

    public function getAllWebhookSecrets(): array
    {
        return array_filter([
            'default' => config('services.stripe.webhook_secret'),
            'ca' => config('services.stripe_ca.webhook_secret'),
            'ie' => config('services.stripe_ie.webhook_secret'),
        ]);
    }

    public function getPrimaryPlatform(): ?StripePlatform
    {
        $primary = config('services.stripe.primary_platform');
        return $primary ? StripePlatform::tryFrom($primary) : null;
    }
}
