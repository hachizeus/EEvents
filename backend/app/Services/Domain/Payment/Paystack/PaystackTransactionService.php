<?php

namespace HiEvents\Services\Domain\Payment\Paystack;

use HiEvents\DomainObjects\Generated\AccountPaystackSettingDomainObjectAbstract;
use HiEvents\DomainObjects\Generated\PaystackPaymentDomainObjectAbstract;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Exceptions\Paystack\PaystackTransactionInitializationException;
use HiEvents\Repository\Interfaces\AccountPaystackSettingRepositoryInterface;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;
use HiEvents\Services\Domain\Payment\Paystack\DTOs\InitializePaystackTransactionDTO;
use HiEvents\Services\Infrastructure\Paystack\PaystackClientFactory;
use HiEvents\Services\Infrastructure\Paystack\PaystackConfigurationService;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Throwable;

class PaystackTransactionService
{
    public function __construct(
        private readonly PaystackClientFactory                     $clientFactory,
        private readonly PaystackConfigurationService              $configurationService,
        private readonly PaystackPaymentsRepositoryInterface       $paystackPaymentsRepository,
        private readonly AccountPaystackSettingRepositoryInterface $accountPaystackSettingRepository,
        private readonly Encrypter                                 $encrypter,
        private readonly LoggerInterface                           $logger,
    ) {
    }

    /**
     * @throws PaystackTransactionInitializationException
     */
    public function initializeTransaction(OrderDomainObject $order, ?int $accountId = null): InitializePaystackTransactionDTO
    {
        $existing = $this->paystackPaymentsRepository->findFirstWhere([
            PaystackPaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
            PaystackPaymentDomainObjectAbstract::STATUS => 'pending',
        ]);

        [$secretKey, $publicKey] = $this->resolveKeysForAccount($accountId);

        if ($existing) {
            return new InitializePaystackTransactionDTO(
                reference: $existing->getReference(),
                accessCode: '',
                authorizationUrl: '',
                publicKey: $publicKey,
            );
        }

        $reference = 'EE-' . strtoupper(Str::random(16));
        $amountInKobo = (int) round($order->getTotalGross() * 100);

        try {
            $client = $this->clientFactory->createWithKey($secretKey);
            $response = $client->post('transaction/initialize', [
                'json' => [
                    'email' => $order->getEmail(),
                    'amount' => $amountInKobo,
                    'reference' => $reference,
                    'currency' => strtoupper($order->getCurrency()),
                    'metadata' => [
                        'order_id' => $order->getId(),
                        'order_short_id' => $order->getShortId(),
                        'event_id' => $order->getEventId(),
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!($data['status'] ?? false)) {
                throw new PaystackTransactionInitializationException(
                    $data['message'] ?? 'Failed to initialize Paystack transaction'
                );
            }

            $transactionData = $data['data'];

            $this->paystackPaymentsRepository->create([
                PaystackPaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
                PaystackPaymentDomainObjectAbstract::REFERENCE => $reference,
                PaystackPaymentDomainObjectAbstract::AMOUNT => $amountInKobo,
                PaystackPaymentDomainObjectAbstract::CURRENCY => strtoupper($order->getCurrency()),
                PaystackPaymentDomainObjectAbstract::STATUS => 'pending',
            ]);

            $this->logger->info('Paystack transaction initialized', [
                'order_id' => $order->getId(),
                'reference' => $reference,
            ]);

            return new InitializePaystackTransactionDTO(
                reference: $reference,
                accessCode: $transactionData['access_code'] ?? '',
                authorizationUrl: $transactionData['authorization_url'] ?? '',
                publicKey: $publicKey,
            );
        } catch (PaystackTransactionInitializationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logger->error('Failed to initialize Paystack transaction', [
                'order_id' => $order->getId(),
                'error' => $e->getMessage(),
            ]);
            throw new PaystackTransactionInitializationException(
                'There was an error communicating with the payment provider. Please try again later.'
            );
        }
    }

    public function verifyTransaction(string $reference, ?int $accountId = null): array
    {
        try {
            [$secretKey] = $this->resolveKeysForAccount($accountId);
            $client = $this->clientFactory->createWithKey($secretKey);
            $response = $client->get('transaction/verify/' . $reference);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['data'] ?? [];
        } catch (Throwable $e) {
            $this->logger->error('Failed to verify Paystack transaction', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Resolve Paystack API keys for an account.
     * Uses the account's own keys if configured, falls back to system keys.
     */
    private function resolveKeysForAccount(?int $accountId): array
    {
        if ($accountId) {
            $setting = $this->accountPaystackSettingRepository->findFirstWhere([
                AccountPaystackSettingDomainObjectAbstract::ACCOUNT_ID => $accountId,
                AccountPaystackSettingDomainObjectAbstract::IS_ACTIVE => true,
            ]);

            if ($setting) {
                $decryptedSecret = $this->encrypter->decrypt($setting->getSecretKey());
                return [$decryptedSecret, $setting->getPublicKey()];
            }
        }

        return [
            $this->configurationService->getSecretKey() ?? '',
            $this->configurationService->getPublicKey() ?? '',
        ];
    }
}
