<?php

namespace HiEvents\Services\Application\Handlers\Account\Payment\Paystack;

use HiEvents\DomainObjects\AccountPaystackSettingDomainObject;
use HiEvents\DomainObjects\Generated\AccountPaystackSettingDomainObjectAbstract;
use HiEvents\Repository\Interfaces\AccountPaystackSettingRepositoryInterface;
use Illuminate\Contracts\Encryption\Encrypter;

readonly class UpsertAccountPaystackSettingHandler
{
    public function __construct(
        private AccountPaystackSettingRepositoryInterface $repository,
        private Encrypter                                 $encrypter,
    ) {
    }

    public function handle(int $accountId, string $publicKey, string $secretKey): AccountPaystackSettingDomainObject
    {
        $existing = $this->repository->findFirstWhere([
            AccountPaystackSettingDomainObjectAbstract::ACCOUNT_ID => $accountId,
        ]);

        $encryptedSecret = $this->encrypter->encrypt($secretKey);

        if ($existing) {
            return $this->repository->updateFromArray($existing->getId(), [
                AccountPaystackSettingDomainObjectAbstract::PUBLIC_KEY => $publicKey,
                AccountPaystackSettingDomainObjectAbstract::SECRET_KEY => $encryptedSecret,
                AccountPaystackSettingDomainObjectAbstract::IS_ACTIVE => true,
            ]);
        }

        return $this->repository->create([
            AccountPaystackSettingDomainObjectAbstract::ACCOUNT_ID => $accountId,
            AccountPaystackSettingDomainObjectAbstract::PUBLIC_KEY => $publicKey,
            AccountPaystackSettingDomainObjectAbstract::SECRET_KEY => $encryptedSecret,
            AccountPaystackSettingDomainObjectAbstract::IS_ACTIVE => true,
        ]);
    }
}
