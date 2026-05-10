<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DomainObjects\AccountPaystackSettingDomainObject;
use HiEvents\Models\AccountPaystackSetting;
use HiEvents\Repository\Interfaces\AccountPaystackSettingRepositoryInterface;

/**
 * @extends BaseRepository<AccountPaystackSettingDomainObject>
 */
class AccountPaystackSettingRepository extends BaseRepository implements AccountPaystackSettingRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountPaystackSetting::class;
    }

    public function getDomainObject(): string
    {
        return AccountPaystackSettingDomainObject::class;
    }
}
