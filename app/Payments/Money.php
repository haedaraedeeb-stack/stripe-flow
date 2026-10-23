<?php

namespace App\Payments;
final class Money
{
    public static function toMinor(string $amount): int
    {
        return (int) bcmul($amount, '100', 0);
    }
}
