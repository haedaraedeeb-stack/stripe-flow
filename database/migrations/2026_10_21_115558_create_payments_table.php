<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_cycle_id')->constrained('contract_cycles')->restrictOnDelete();
            $table->string('method');
            $table->string('status')->default('initiated');;
            $table->decimal('amount-usd', 12, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('provider_ref')->nullable()->index();
            $table->string('provider_payment_ref')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
