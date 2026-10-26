<?php

namespace App\Listeners;

use App\Enums\ContractCycleStatus;
use App\Events\PaymentSucceeded;
use App\Models\ContractCycle;
use Illuminate\Support\Facades\DB;

class ActivateOrRenewContract
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentSucceeded $event): void
    {
        DB::transaction(function () use ($event){
            $cycle = ContractCycle::lockForUpdate()->findOrFail($event->payment->contract_cycle_id);
            if($cycle->status === ContractCycleStatus::Awaiting_Payment){
                $cycle->update(['status' => ContractCycleStatus::Open ,
                'starts_at' => now(),
                'ends_at'   => now()->addDays(30),]);
            }
        });
    }
}
