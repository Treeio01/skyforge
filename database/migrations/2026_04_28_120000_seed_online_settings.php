<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'online.enabled', 'value' => '0', 'type' => 'boolean', 'description' => 'Включить накрутку онлайна'],
            ['key' => 'online.min', 'value' => '1500', 'type' => 'integer', 'description' => 'Минимум фейкового онлайна'],
            ['key' => 'online.max', 'value' => '1600', 'type' => 'integer', 'description' => 'Максимум фейкового онлайна'],
            ['key' => 'online.tick_seconds', 'value' => '8', 'type' => 'integer', 'description' => 'Период обновления онлайна (сек)'],
            ['key' => 'online.max_step', 'value' => '3', 'type' => 'integer', 'description' => 'Максимальный шаг дрейфа онлайна'],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row + ['updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'online.enabled',
            'online.min',
            'online.max',
            'online.tick_seconds',
            'online.max_step',
        ])->delete();
    }
};
