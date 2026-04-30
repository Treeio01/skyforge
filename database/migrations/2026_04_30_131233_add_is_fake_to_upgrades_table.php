<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('upgrades', 'is_fake')) {
            Schema::table('upgrades', function (Blueprint $table) {
                $table->boolean('is_fake')->default(false)->after('is_revealed');
            });
        }

        if (! $this->hasIndex('upgrades', 'upgrades_is_fake_created_at_index')) {
            Schema::table('upgrades', function (Blueprint $table) {
                $table->index(['is_fake', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('upgrades', 'is_fake')) {
            return;
        }

        Schema::table('upgrades', function (Blueprint $table) {
            if ($this->hasIndex('upgrades', 'upgrades_is_fake_created_at_index')) {
                $table->dropIndex(['is_fake', 'created_at']);
            }

            $table->dropColumn('is_fake');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
