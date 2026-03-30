<?php

declare(strict_types=1);

use App\Models\Skin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;

uses(LazilyRefreshDatabase::class);

it('imports skins from json file', function () {
    $json = json_encode([
        'AK-47 | Redline (Field-Tested)' => [
            'file' => 'AK-47 | Redline (Field-Tested).webp',
            'price' => 12.50,
        ],
        '★ Karambit | Doppler (Factory New)' => [
            'file' => 'Karambit Doppler.webp',
            'price' => 1500.00,
        ],
        'Sticker | karrigan | Paris 2023' => [
            'file' => 'Sticker karrigan.webp',
            'price' => 0.03,
        ],
    ]);

    $path = storage_path('app/test_skins.json');
    File::put($path, $json);

    $this->artisan('skins:import', ['path' => $path])
        ->assertSuccessful();

    expect(Skin::count())->toBe(3);

    $ak = Skin::where('market_hash_name', 'AK-47 | Redline (Field-Tested)')->first();
    expect($ak)
        ->weapon_type->toBe('AK-47')
        ->skin_name->toBe('Redline')
        ->exterior->value->toBe('FT')
        ->price->toBe(1250);

    $karambit = Skin::where('market_hash_name', 'LIKE', '%Karambit%')->first();
    expect($karambit)
        ->category->value->toBe('knife')
        ->price->toBe(150000);

    File::delete($path);
});

it('fails with missing file', function () {
    $this->artisan('skins:import', ['path' => '/nonexistent/file.json'])
        ->assertFailed();
});

it('upserts on duplicate market_hash_name', function () {
    $json = json_encode([
        'AK-47 | Redline (Field-Tested)' => [
            'file' => 'ak.webp',
            'price' => 10.00,
        ],
    ]);

    $path = storage_path('app/test_skins_upsert.json');
    File::put($path, $json);

    $this->artisan('skins:import', ['path' => $path])->assertSuccessful();
    expect(Skin::count())->toBe(1);
    expect(Skin::first()->price)->toBe(1000);

    $json2 = json_encode([
        'AK-47 | Redline (Field-Tested)' => [
            'file' => 'ak.webp',
            'price' => 15.00,
        ],
    ]);
    File::put($path, $json2);

    $this->artisan('skins:import', ['path' => $path])->assertSuccessful();
    expect(Skin::count())->toBe(1);
    expect(Skin::first()->price)->toBe(1500);

    File::delete($path);
});

it('dumps skins to sql file', function () {
    Skin::factory()->count(3)->create();

    $dumpPath = 'database/dumps/test_dump.sql';

    $this->artisan('skins:dump', ['--path' => $dumpPath])
        ->assertSuccessful();

    $fullPath = base_path($dumpPath);
    expect(File::exists($fullPath))->toBeTrue();

    $content = File::get($fullPath);
    expect($content)
        ->toContain('INSERT INTO `skins`')
        ->toContain('ON DUPLICATE KEY UPDATE');

    File::delete($fullPath);
});
