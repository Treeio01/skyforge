<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('preserves type when updating existing setting', function () {
    Setting::create(['key' => 'foo.bar', 'value' => '42', 'type' => 'integer']);

    Setting::set('foo.bar', 99);

    $row = Setting::where('key', 'foo.bar')->first();
    expect($row->type)->toBe('integer');
    expect(Setting::get('foo.bar'))->toBe(99);
});

it('accepts explicit type on set', function () {
    Setting::set('flag.x', true, 'boolean');

    $row = Setting::where('key', 'flag.x')->first();
    expect($row->type)->toBe('boolean');
    expect(Setting::get('flag.x'))->toBeTrue();
});
