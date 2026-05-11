<?php

namespace HiEvents\DomainObjects\Enums;

enum PaymentProviders: string
{
    use BaseEnum;

    case PAYSTACK = 'PAYSTACK';
    case OFFLINE = 'OFFLINE';
    /** @deprecated Stripe is no longer the primary payment provider. Kept for backward compatibility. */
    case STRIPE = 'STRIPE';
}
