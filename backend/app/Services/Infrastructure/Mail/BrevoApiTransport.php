<?php

namespace HiEvents\Services\Infrastructure\Mail;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

/**
 * Brevo (Sendinblue) HTTP API transport for Laravel.
 * Uses HTTPS port 443 — works on all hosting providers including Render free tier.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(
        private readonly string $apiKey,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($dispatcher, $logger);
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromAddresses = $email->getFrom();
        $from = count($fromAddresses) > 0 ? $fromAddresses[0] : null;

        $toAddresses = $email->getTo();
        $to = array_map(fn(Address $a) => [
            'email' => $a->getAddress(),
            'name' => $a->getName() ?: $a->getAddress(),
        ], $toAddresses);

        $payload = [
            'sender' => [
                'email' => $from?->getAddress() ?? config('mail.from.address'),
                'name' => $from?->getName() ?: config('mail.from.name'),
            ],
            'to' => $to,
            'subject' => $email->getSubject() ?? '(no subject)',
            'htmlContent' => $email->getHtmlBody() ?? $email->getTextBody() ?? '',
            'textContent' => $email->getTextBody() ?? '',
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'api-key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('Brevo API cURL error: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $data = json_decode($response, true);
            throw new \RuntimeException(
                'Brevo API error (' . $httpCode . '): ' . ($data['message'] ?? $response)
            );
        }
    }

    public function __toString(): string
    {
        return 'brevo+api://api.brevo.com';
    }
}
