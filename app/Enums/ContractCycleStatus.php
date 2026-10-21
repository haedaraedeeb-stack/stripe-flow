<?php

namespace App\Enums;

enum ContractCycleStatus: string
{
    case Awaiting_Payment = 'awaiting_payment';
    case Open = 'open';
    case Closed = 'closed';
}
