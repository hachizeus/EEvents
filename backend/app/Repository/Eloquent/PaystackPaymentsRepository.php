<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DomainObjects\PaystackPaymentDomainObject;
use HiEvents\Models\PaystackPayment;
use HiEvents\Repository\Interfaces\PaystackPaymentsRepositoryInterface;

/**
 * @extends BaseRepository<PaystackPaymentDomainObject>
 */
class PaystackPaymentsRepository extends BaseRepository implements PaystackPaymentsRepositoryInterface
{
    protected function getModel(): string
    {
        return PaystackPayment::class;
    }

    public function getDomainObject(): string
    {
        return PaystackPaymentDomainObject::class;
    }
}
