<?php
namespace App\Payments\Contracts;

use App\Payments\DTO\CreatePaymentData;
use App\Payments\DTO\GatewayEvent;
use App\Payments\DTO\GatewayPaymentResult;
use App\Payments\Exceptions\GatewayRejectedException;
use App\Payments\Exceptions\GatewayUnavailableException;

interface PaymentGateway
{
    /**
     * @throws GatewayUnavailableException
     * @throws GatewayRejectedException
     */

    public function createPayment(CreatePaymentData $data): GatewayPaymentResult;
    public function parseWebhook(string $rowBody, array $headers): ?GatewayEvent;
}
