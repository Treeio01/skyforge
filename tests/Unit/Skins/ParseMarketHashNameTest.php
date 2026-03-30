<?php

declare(strict_types=1);

use App\Services\SkinNameParser;

it('parses weapon skin with exterior', function () {
    $result = SkinNameParser::parse('AK-47 | Redline (Field-Tested)');

    expect($result)
        ->weapon_type->toBe('AK-47')
        ->skin_name->toBe('Redline')
        ->exterior->toBe('FT')
        ->category->toBe('weapon');
});

it('parses knife with star prefix', function () {
    $result = SkinNameParser::parse('★ Karambit | Doppler (Factory New)');

    expect($result)
        ->weapon_type->toBe('Karambit')
        ->skin_name->toBe('Doppler')
        ->exterior->toBe('FN')
        ->category->toBe('knife');
});

it('parses gloves', function () {
    $result = SkinNameParser::parse('★ Sport Gloves | Hedge Maze (Minimal Wear)');

    expect($result)
        ->weapon_type->toBe('Sport Gloves')
        ->skin_name->toBe('Hedge Maze')
        ->exterior->toBe('MW')
        ->category->toBe('gloves');
});

it('parses sticker', function () {
    $result = SkinNameParser::parse('Sticker | karrigan | Paris 2023');

    expect($result)
        ->weapon_type->toBeNull()
        ->skin_name->toBe('karrigan | Paris 2023')
        ->exterior->toBeNull()
        ->category->toBe('sticker');
});

it('parses graffiti', function () {
    $result = SkinNameParser::parse('Sealed Graffiti | Dragon (Blood Red)');

    expect($result)
        ->weapon_type->toBeNull()
        ->skin_name->toBe('Dragon')
        ->category->toBe('graffiti');
});

it('parses all exteriors', function (string $label, string $code) {
    $result = SkinNameParser::parse("M4A4 | Asiimov ({$label})");

    expect($result)->exterior->toBe($code);
})->with([
    ['Factory New', 'FN'],
    ['Minimal Wear', 'MW'],
    ['Field-Tested', 'FT'],
    ['Well-Worn', 'WW'],
    ['Battle-Scarred', 'BS'],
]);

it('handles skin without exterior', function () {
    $result = SkinNameParser::parse('Music Kit | Halo, The Master Chief Collection');

    expect($result)
        ->exterior->toBeNull()
        ->category->toBe('weapon');
});
