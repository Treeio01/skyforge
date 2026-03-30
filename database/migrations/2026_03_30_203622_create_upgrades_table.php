<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('target_skin_id')->constrained('skins');
            $table->unsignedBigInteger('bet_amount');
            $table->unsignedBigInteger('balance_amount')->default(0);
            $table->unsignedBigInteger('target_price');
            $table->decimal('chance', 8, 5);
            $table->decimal('multiplier', 8, 2);
            $table->decimal('house_edge', 5, 2);
            $table->decimal('chance_modifier', 5, 3)->default(1.000);
            $table->string('result', 8);
            $table->double('roll_value');
            $table->string('roll_hex', 16);
            $table->string('client_seed', 64);
            $table->string('server_seed', 128);
            $table->string('server_seed_raw', 128);
            $table->unsignedBigInteger('nonce');
            $table->boolean('is_revealed')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('result');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upgrades');
    }
};
