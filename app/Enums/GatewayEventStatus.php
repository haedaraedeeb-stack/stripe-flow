<?php

namespace App\Enums;

enum GatewayEventStatus: string
{
    case Received = 'received';
    case Processing = 'processing';
}
