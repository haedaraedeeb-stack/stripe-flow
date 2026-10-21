<?php

namespace App\Enums;

enum GatewayEventType: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Expired = 'expired';
}
