<?php

namespace HiEvents\Providers;

use HiEvents\Services\Infrastructure\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('brevo', function (array $config) {
            $apiKey = $config['api_key'] ?? env('BREVO_API_KEY');
            return new BrevoApiTransport($apiKey);
        });
    }
}
