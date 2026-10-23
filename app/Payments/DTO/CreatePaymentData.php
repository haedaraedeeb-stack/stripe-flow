<?php

namespace App\Payments\DTO;

final readonly class CreatePaymentData {
    public function __construct(
        public int $paymentId,
        public string $amount,
        public string $currency,
        public string $description,
        public string $successUrl,
        public string $cancelUrl,
        public string $idempotencyKey,
        public int $expiresAt,
    ){}
}
