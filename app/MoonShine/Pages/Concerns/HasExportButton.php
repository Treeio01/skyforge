<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Concerns;

use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

trait HasExportButton
{
    abstract protected function exportRoute(): string;

    protected function topRightButtons(): ListOf
    {
        return parent::topRightButtons()
            ->prepend(
                ActionButton::make('Экспорт CSV', fn () => $this->exportRoute())
                    ->customAttributes(['target' => '_blank'])
                    ->icon('arrow-down-tray'),
            );
    }
}
