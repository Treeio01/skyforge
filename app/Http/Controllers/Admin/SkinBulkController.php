<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Skin;

class SkinBulkController extends BulkController
{
    protected function model(): string
    {
        return Skin::class;
    }
}
