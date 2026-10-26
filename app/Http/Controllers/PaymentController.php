<?php

namespace App\Http\Controllers;

use App\Models\ContractCycle;
use App\Models\Payment;
use App\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PaymentController
{
    public function store(Request $request, ContractCycle $cycle, PaymentService $service)
    {
        abort_unless($cycle->user_id === $request->user()->id, 404);

        $data = $request->validate(['method' => ['required', Rule::in(array_keys(config('payments.gateways')))]]);

        $key = $request->header('Idempotency-Key');
        abort_if(! $key || strlen($key) > 100, 422, 'Idempotency-Key header is required');

        return $this->present($service->initiate($cycle, $data['method'], $request->user(), $key));
    }

    public function show(Request $request, Payment $payment)
    {
        abort_unless($payment->cycle->user_id === $request->user()->id, 404);
        return $this->present($payment);
    }

    private function present(Payment $p): array
    {
        return [
            'id' => $p->id, 'status' => $p->status->value, 'method' => $p->method,
            'amount-usd' => $p->amount_usd, 'redirect_url' => $p->redirect_url,
        ];
    }
}
