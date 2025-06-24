<?php

namespace App\Services\Management\Client\Order\Payment;

enum PaymentStatusEnum: int
{
    // An order was successfully registered, but is'nt paid yet
    case CREATED = 0;

    // An order's amount was successfully holded (for two-stage payments only)
    case APPROVED = 1;

    // An order was deposited
    // If you want to check whether payment was successfully paid - use this constant
    case DEPOSITED = 2;

    // An order was reversed
    case REVERSED = 3;

    // An order was refunded
    case REFUNDED = 4;

    // An order authorization was initialized by card emitter's ACS
    case AUTHORIZATION_INITIALIZED = 5;

    // An order was declined
    case DECLINED = 6;
}
