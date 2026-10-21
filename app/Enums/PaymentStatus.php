<?php

namespace App\Enums;

use phpDocumentor\Reflection\Types\Self_;

enum PaymentStatus: string
{
    case Initiated = 'initiated';
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Expired = 'expired';

    public function canTransitionTo(self $to): bool {
        return match ($this) {
          self::Initiated , self::Pending
          => in_array($to, [self::Pending, self::Succeeded, self::Failed, self::Expired], true),
            self::Failed => $to === self::Succeeded,
            self::Succeeded , self::Expired => false,
        };
    }
}
