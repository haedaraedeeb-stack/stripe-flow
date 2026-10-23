<?php
namespace App\Payments\Contracts;

use App\Payments\DTO\CreatePaymentData;
use App\Payments\DTO\GatewayEvent;
use App\Payments\DTO\GatewayPaymentResult;

interface PaymentGateway
{
    public function createPayment(CreatePaymentData $data): GatewayPaymentResult;
    public function parseWebhook(string $rowBody, array $headers): ?GatewayEvent;
}
