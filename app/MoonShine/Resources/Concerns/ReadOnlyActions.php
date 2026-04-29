<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Concerns;

use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

trait ReadOnlyActions
{
    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(
            Action::CREATE,
            Action::UPDATE,
            Action::DELETE,
            Action::MASS_DELETE,
        );
    }
}
