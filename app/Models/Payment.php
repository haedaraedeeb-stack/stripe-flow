<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_cycle_id', 'method', 'status', 'amount_usd', 'currency',
        'provider_ref', 'provider_payment_ref', 'redirect_url',
        'failure_reason', 'idempotency_key', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => PaymentStatus::class,
            'amount_usd'   => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }
    public function cycle(): BelongsTo
    {
        return $this->belongsTo(ContractCycle::class, 'contract_cycle_id');
    }
}
