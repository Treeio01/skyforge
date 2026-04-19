<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MoonShine\Laravel\Models\MoonshineUser;

class MoonShineAdminSeeder extends Seeder
{
    public function run(): void
    {
        MoonshineUser::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ],
        );
    }
}
