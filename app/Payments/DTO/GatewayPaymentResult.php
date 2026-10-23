<?php

namespace App\Payments\DTO;
final readonly class GatewayPaymentResult
{
    public function __construct(
        public string $providerRef,
        public ?string $redirectUrl = null,
        public array $instructions = [],
    ) {}
}
