<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\FaqItem;

class FaqBulkController extends BulkController
{
    protected function model(): string
    {
        return FaqItem::class;
    }
}
