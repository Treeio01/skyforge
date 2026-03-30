<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skins', function (Blueprint $table) {
            $table->id();
            $table->string('market_hash_name')->unique();
            $table->string('weapon_type', 64)->nullable();
            $table->string('skin_name')->nullable();
            $table->string('exterior', 4)->nullable();
            $table->string('rarity', 32)->nullable();
            $table->string('rarity_color', 7)->nullable();
            $table->string('category', 64)->nullable();
            $table->string('image_path');
            $table->unsignedBigInteger('price')->default(0);
            $table->timestamp('price_updated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available_for_upgrade')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'price']);
            $table->index(['is_active', 'is_available_for_upgrade', 'price']);
            $table->fullText('market_hash_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skins');
    }
};
