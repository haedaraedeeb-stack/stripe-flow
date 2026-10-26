<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use App\Payments\Exceptions\InvalidWebhookException;
use App\Payments\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class WebhookController
{
    public function __invoke(Request $request, string $provider, PaymentGatewayManager $gateways)
    {
        $headers = collect($request->headers->all())->map (fn ($v) => $v[0])->all();
        try {
            $event = $gateways->driver($provider)->parseWebhook($request->getContent(), $headers);
        } catch (InvalidWebhookException) {
            Log::warning('webhook.invalid_signature', ['provoider' => $provider, 'ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        if ($event === null) {
            return response()->json(['received' => true]);
        }
        $record = WebhookEvent::firstOrCreate(
            ['provider' => $provider, 'event_id' => $event->id],
            ['type' => $event->type->value, 'payload' => $event->toArray()],
        );

        if ($record->status !== 'processed') {
            ProcessWebhookEvent::dispatch($record->id)->onQueue('critical');
        }
        return response()->json(['received' => true]);
    }
}
