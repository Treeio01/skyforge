<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skin_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skin_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price');
            $table->string('source', 32)->default('market_csgo');
            $table->timestamp('fetched_at');

            $table->index(['skin_id', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skin_prices');
    }
};
