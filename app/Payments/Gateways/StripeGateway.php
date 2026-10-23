<?php

namespace App\Payments\Gateways;

use App\Enums\GatewayEventType;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTO\CreatePaymentData;
use App\Payments\DTO\GatewayEvent;
use App\Payments\DTO\GatewayPaymentResult;
use App\Payments\Exceptions\InvalidWebhookException;
use App\Payments\Money;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

final class StripeGateway implements PaymentGateway {
    public function __construct(
        private StripeClient $client,
        private string $webhookSecret,
    ){}

    public function createPayment(CreatePaymentData $data): GatewayPaymentResult
    {
        $meta = ['payment_id' => (string) $data->paymentId ];
        $session = $this->client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $data->currency,
                    'unit_amount' => Money::toMinor($data->amount),
                    'product_data' => ['name' => $data->description],
                ],
            ]],
            'metadata' => $meta,
            'payment_intent_data' => ['metadata' => $meta],
            'success_url' => $data->successUrl,
            'cancel_url' => $data->cancelUrl,
            'expires_at' => time() + 31 * 60,
            ], [
                'idempotency_key' => $data->idempotencyKey,
        ]);
        return new GatewayPaymentResult(providerRef: $session->id, redirectUrl: $session->url);
    }

    public function parseWebhook(string $rowBody, array $headers): ?GatewayEvent
    {
        try {
            $event = Webhook::constructEvent($rowBody,
                $headers['stripe-signature'] ?? '',
                $this->webhookSecret
            );
        }
        catch (\UnexpectedValueException | SignatureVerificationException) {
            throw new InvalidWebhookException();
        }
        $stripeObject = $event->data->object;
        $pid = isset($stripeObject->metadata->payment_id) ? (int) $stripeObject->metadata->payment_id : null;
        return match ($event->type) {
            'checkout.session.completed' => $stripeObject->payment_status === 'paid'
                ? new GatewayEvent($event->id, 'stripe', GatewayEventType::Succeeded, $pid,
                    providerRef: $stripeObject->id, paymentRef: $stripeObject->payment_intent,
                    amountMinor: $stripeObject->amount_total, currency: $stripeObject->currency)
                : null,
            'checkout.session.expired' =>
            new GatewayEvent($event->id, 'stripe', GatewayEventType::Expired, $pid, providerRef: $stripeObject->id),
            'payment_intent.payment_failed' =>
            new GatewayEvent($event->id, 'stripe', GatewayEventType::Failed, $pid,
                paymentRef: $stripeObject->id, failureReason: $o->last_payment_error->message ?? null),
            default => null,
        };
    }
}
