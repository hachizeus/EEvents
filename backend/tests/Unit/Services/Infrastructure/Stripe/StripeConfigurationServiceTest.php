<?php

namespace Tests\Unit\Services\Infrastructure\Stripe;

use HiEvents\Services\Infrastructure\Stripe\StripeConfigurationService;
use Tests\TestCase;

class StripeConfigurationServiceTest extends TestCase
{
    private StripeConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StripeConfigurationService();
    }

    public function test_get_secret_key_returns_default(): void
    {
        config(['services.stripe.secret_key' => 'sk_test']);

        $result = $this->service->getSecretKey();

        $this->assertEquals('sk_test', $result);
    }

    public function test_get_public_key_returns_default(): void
    {
        config(['services.stripe.public_key' => 'pk_test']);

        $result = $this->service->getPublicKey();

        $this->assertEquals('pk_test', $result);
    }

    public function test_get_webhook_secret_returns_default(): void
    {
        config(['services.stripe.webhook_secret' => 'wh_test']);

        $result = $this->service->getWebhookSecret();

        $this->assertEquals('wh_test', $result);
    }
}
