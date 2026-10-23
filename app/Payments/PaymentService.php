<?php

namespace App\Payments;

use App\Enums\ContractCycleStatus;
use App\Enums\PaymentStatus;
use App\Models\ContractCycle;
use App\Models\Payment;
use App\Models\User;
use App\Payments\DTO\CreatePaymentData;
use App\Payments\Exceptions\GatewayRejectedException;
use App\Payments\Exceptions\GatewayUnavailableException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class PaymentService
{
    public function __construct(private PaymentGatewayManager $gateways) {}

    public function initiate(ContractCycle $cycle, string $method, User $user, string $clientKey): Payment
    {
        $key = $user->id . ':' . $clientKey;
        try {
            $payment = DB::transaction(fn () => $this->createOrReuse($cycle, $method, $key));
        } catch (UniqueConstraintViolationException) {
            $payment = $this->assertSameRequest(
                Payment::where('idempotency_key', $key)->firstOrFail(), $cycle->id, $method
            );
        }
        if ($payment->provider_ref === null) {
            $this->sendToGateway($payment);
        }

        return $payment->fresh();
    }

    private function createOrReuse(ContractCycle $cycle, string $method, string $key): Payment
    {
        $cycle = ContractCycle::lockForUpdate()->findOrFail($cycle->id);
        if ($same = Payment::where('idempotency_key', $key)->first()) {
            return $this->assertSameRequest($same, $cycle->id, $method);
        }
        abort_unless($cycle->status === ContractCycleStatus::Awaiting_Payment, 409, 'CYCLE_NOT_PAYABLE');

        $live = Payment::where('contract_cycle_id', $cycle->id)
            ->whereIn('status', [PaymentStatus::Initiated->value, PaymentStatus::Pending->value])
            ->where('created_at', '>', now()->subMinutes(30))
            ->first();

        if ($live) {
            abort_if($live->method !== $method, 409, 'PAYMENT_IN_PROGRESS');
            return $live;
        }

        return Payment::create([
            'contract_cycle_id' => $cycle->id,
            'method'            => $method,
            'amount_usd'        => $cycle->amount_usd,
            'currency'          => 'usd',
            'idempotency_key'   => $key,
        ]);
    }

    private function assertSameRequest(Payment $p, int $cycleId, string $method): Payment
    {
        abort_if(
            (int) $p->contract_cycle_id !== $cycleId || $p->method !== $method,
            409, 'IDEMPOTENCY_KEY_REUSED'
        );
        return $p;
    }
    private function sendToGateway(Payment $payment): void
    {
        try {
            $result = $this->gateways->driver($payment->method)->createPayment(new CreatePaymentData(
                paymentId: $payment->id,
                amount: $payment->amount_usd,
                currency: $payment->currency,
                description: "Kabtin – cycle #{$payment->contract_cycle_id}",
                successUrl: config('app.frontend_url') . "/payment/result?payment_id={$payment->id}",
                cancelUrl:  config('app.frontend_url') . "/payment/cancelled?payment_id={$payment->id}",
                idempotencyKey: "payment-{$payment->id}",
                expiresAt: $payment->created_at->addMinutes(31)->timestamp,
            ));
        } catch (GatewayUnavailableException $e) {
            Log::warning('payment.gateway_unavailable', ['payment' => $payment->id]);
            abort(503, 'PROVIDER_UNAVAILABLE');
        } catch (GatewayRejectedException $e) {
            Log::critical('payment.gateway_rejected', ['payment' => $payment->id]);
            $payment->update([
                'status'         => PaymentStatus::Failed->value,
                'failure_reason' => 'gateway_rejected',
            ]);
            abort(502, 'PROVIDER_REJECTED');
        }

        Payment::whereKey($payment->id)
            ->where('status', PaymentStatus::Initiated->value)
            ->update([
                'provider_ref' => $result->providerRef,
                'redirect_url' => $result->redirectUrl,
                'status'       => PaymentStatus::Pending->value,
            ]);
    }
}
