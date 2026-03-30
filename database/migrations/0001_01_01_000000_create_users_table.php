<?php

declare(strict_types=1);

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('steam_id', 20)->unique();
            $table->string('username');
            $table->string('avatar_url', 512)->nullable();
            $table->string('trade_url', 512)->nullable();
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('total_deposited')->default(0);
            $table->unsignedBigInteger('total_withdrawn')->default(0);
            $table->unsignedBigInteger('total_upgraded')->default(0);
            $table->unsignedBigInteger('total_won')->default(0);
            $table->boolean('is_banned')->default(false);
            $table->string('ban_reason', 512)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->decimal('house_edge_override', 5, 2)->nullable();
            $table->decimal('chance_modifier', 5, 3)->default(1.000);
            $table->timestamp('last_active_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
