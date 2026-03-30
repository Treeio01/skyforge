<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_skins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('skin_id')->constrained();
            $table->unsignedBigInteger('price_at_acquisition');
            $table->string('source', 32);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status', 32)->default('available');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('skin_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skins');
    }
};
