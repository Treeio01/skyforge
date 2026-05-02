<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_bot')->default(false)->after('is_admin');
            $table->index(['is_bot']);
        });

        // Backfill: fake users created by feed:fake have synthetic steam_id
        // prefixed with '9' (real Steam IDs are 17-digit and start with 7656).
        User::query()->where('steam_id', 'like', '9%')->update(['is_bot' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_bot']);
            $table->dropColumn('is_bot');
        });
    }
};
