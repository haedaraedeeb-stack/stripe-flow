<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case  Stripe = 'stripe';
    case Manual = 'manual';
}
