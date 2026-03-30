<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_skin_id')->constrained();
            $table->foreignId('skin_id')->constrained();
            $table->unsignedBigInteger('amount');
            $table->string('status', 32)->default('pending');
            $table->string('trade_offer_id', 64)->nullable();
            $table->string('trade_offer_status', 32)->nullable();
            $table->string('failure_reason', 512)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('trade_offer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
