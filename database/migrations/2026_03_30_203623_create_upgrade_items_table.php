<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upgrade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upgrade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_skin_id')->constrained();
            $table->foreignId('skin_id')->constrained();
            $table->unsignedBigInteger('price');

            $table->index('upgrade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upgrade_items');
    }
};
