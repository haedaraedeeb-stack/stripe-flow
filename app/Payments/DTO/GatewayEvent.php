<?php

namespace App\Payments\DTO;

use App\Enums\GatewayEventType;

final readonly class GatewayEvent
{
    public function __construct(
        public string $id,
        public string $provider,
        public GatewayEventType $type,
        public ?int $paymentId,
        public ?string $providerRef = null,
        public ?string $paymentRef = null,
        public ?int $amountMinor = null,
        public ?string $currency = null,
        public ?string $failureReason = null,
    ){}

    public function toArray(): array {
        return [...get_object_vars($this), 'type' => $this->type->value];
    }
    public static function fromArray(array $a): self {
        return new self(...[...$a, 'type' => GatewayEventType::from($a['type'])]);
    }
}
