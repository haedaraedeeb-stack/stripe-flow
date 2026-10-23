<?php

namespace App\Models;

use App\Enums\ContractCycleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractCycle extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount_usd', 'status', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'status'     => ContractCycleStatus::class,
            'amount_usd' => 'decimal:2',
            'starts_at'  => 'datetime',
            'ends_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
