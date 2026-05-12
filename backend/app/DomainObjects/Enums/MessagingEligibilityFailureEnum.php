<?php

namespace HiEvents\DomainObjects\Enums;

enum MessagingEligibilityFailureEnum: string
{
    case PAYMENT_NOT_CONNECTED = 'stripe_not_connected'; // value kept for DB backward compat
    case NO_PAID_ORDERS = 'no_paid_orders';
    case EVENT_TOO_NEW = 'event_too_new';
}
