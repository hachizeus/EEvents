<?php

namespace HiEvents\DomainObjects;

/**
 * @deprecated Stripe is no longer the primary payment provider.
 * Kept for backward compatibility with existing Stripe payment records.
 */
class StripePaymentDomainObject extends Generated\StripePaymentDomainObjectAbstract
{
}
