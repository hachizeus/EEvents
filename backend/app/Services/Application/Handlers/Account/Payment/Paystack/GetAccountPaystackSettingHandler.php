<?php

namespace HiEvents\Services\Application\Handlers\Account\Payment\Paystack;

use HiEvents\DomainObjects\AccountPaystackSettingDomainObject;
use HiEvents\DomainObjects\Generated\AccountPaystackSettingDomainObjectAbstract;
use HiEvents\Repository\Interfaces\AccountPaystackSettingRepositoryInterface;

readonly class GetAccountPaystackSettingHandler
{
    public function __construct(
        private AccountPaystackSettingRepositoryInterface $repository,
    ) {
    }

    public function handle(int $accountId): ?AccountPaystackSettingDomainObject
    {
        return $this->repository->findFirstWhere([
            AccountPaystackSettingDomainObjectAbstract::ACCOUNT_ID => $accountId,
        ]);
    }
}
