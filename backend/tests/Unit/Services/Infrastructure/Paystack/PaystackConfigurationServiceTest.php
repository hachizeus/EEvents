<?php

namespace Tests\Unit\Services\Infrastructure\Paystack;

use HiEvents\Services\Infrastructure\Paystack\PaystackConfigurationService;
use Tests\TestCase;

class PaystackConfigurationServiceTest extends TestCase
{
    private PaystackConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaystackConfigurationService();
    }

    public function test_get_secret_key_returns_default(): void
    {
        config(['services.paystack.secret_key' => 'sk_test']);
        
        $result = $this->service->getSecretKey();
        
        $this->assertEquals('sk_test', $result);
    }

    public function test_get_public_key_returns_default(): void
    {
        config(['services.paystack.public_key' => 'pk_test']);
        
        $result = $this->service->getPublicKey();
        
        $this->assertEquals('pk_test', $result);
    }

    public function test_get_webhook_secret_returns_default(): void
    {
        config(['services.paystack.webhook_secret' => 'wh_test']);
        
        $result = $this->service->getWebhookSecret();
        
        $this->assertEquals('wh_test', $result);
    }
}