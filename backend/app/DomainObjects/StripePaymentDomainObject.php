<?php

namespace HiEvents\DomainObjects;

use HiEvents\DomainObjects\Enums\StripePlatform;

class StripePaymentDomainObject extends Generated\StripePaymentDomainObjectAbstract
{
    private ?OrderDomainObject $order = null;

    public function getOrder(): ?OrderDomainObject
    {
        return $this->order;
    }

    public function setOrder(?OrderDomainObject $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getStripePlatformEnum(): ?StripePlatform
    {
        return StripePlatform::fromString($this->getStripePlatform());
    }
}
