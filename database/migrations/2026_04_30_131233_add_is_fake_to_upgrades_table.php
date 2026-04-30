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
        Schema::table('upgrades', function (Blueprint $table) {
            $table->boolean('is_fake')->default(false)->after('is_revealed');
            $table->index(['is_fake', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upgrades', function (Blueprint $table) {
            $table->dropIndex(['is_fake', 'created_at']);
            $table->dropColumn('is_fake');
        });
    }
};
