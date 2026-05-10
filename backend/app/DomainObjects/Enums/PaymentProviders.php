<?php

namespace HiEvents\DomainObjects\Enums;

enum PaymentProviders: string
{
    use BaseEnum;

    case PAYSTACK = 'PAYSTACK';
    case OFFLINE = 'OFFLINE';
}
