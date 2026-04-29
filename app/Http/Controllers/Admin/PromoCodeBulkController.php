<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\PromoCode;

class PromoCodeBulkController extends BulkController
{
    protected function model(): string
    {
        return PromoCode::class;
    }
}
