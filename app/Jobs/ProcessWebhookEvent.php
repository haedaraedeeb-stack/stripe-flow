<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use App\Payments\DTO\GatewayEvent;
use App\Payments\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\DB;

#[Tries(5)]
#[Backoff([30,30,120,300])]
class ProcessWebhookEvent implements ShouldQueue
{
    use Dispatchable ,Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $webhookEventId){}
    /**
     * Execute the job.
     */
    public function handle(PaymentService $payments): void
    {
        DB::transaction(function () use ($payments) {
            $record = WebhookEvent::lockForUpdate()->findOrFail($this->webhookEventId);
            if ($record->status === 'processed') {
                return;
            }
            $payments->applyGatewayEvent(GatewayEvent::fromArray($record->payload));
            $record->update(['status' => 'processed', 'processed_at' => now()]);
        });
    }
}
