<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\OnlineSettingsPage;
use App\MoonShine\Pages\SiteSettingsPage;
use App\MoonShine\Resources\Deposit\DepositResource;
use App\MoonShine\Resources\FaqCategoryMoon\FaqCategoryMoonResource;
use App\MoonShine\Resources\FaqItemMoon\FaqItemMoonResource;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\MoonShine\Resources\Setting\SettingResource;
use App\MoonShine\Resources\Skin\SkinResource;
use App\MoonShine\Resources\Transaction\TransactionResource;
use App\MoonShine\Resources\Upgrade\UpgradeResource;
use App\MoonShine\Resources\User\UserResource;
use App\MoonShine\Resources\UtmMark\UtmMarkResource;
use App\MoonShine\Resources\Withdrawal\WithdrawalResource;
use App\MoonShine\SkyforgePalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

final class SkyforgeLayout extends AppLayout
{
    /** @var null|class-string<PaletteContract> */
    protected ?string $palette = SkyforgePalette::class;

    protected function menu(): array
    {
        return [
            MenuGroup::make('Пользователи', [
                MenuItem::make(UserResource::class, 'Пользователи'),
            ]),

            MenuGroup::make('Игра', [
                MenuItem::make(SkinResource::class, 'Скины'),
                MenuItem::make(UpgradeResource::class, 'Апгрейды'),
            ]),

            MenuGroup::make('Финансы', [
                MenuItem::make(TransactionResource::class, 'Транзакции'),
                MenuItem::make(DepositResource::class, 'Депозиты'),
                MenuItem::make(WithdrawalResource::class, 'Выводы'),
            ]),

            MenuGroup::make('Контент', [
                MenuItem::make(FaqCategoryMoonResource::class, 'FAQ Категории'),
                MenuItem::make(FaqItemMoonResource::class, 'FAQ Вопросы'),
                MenuItem::make(PromoCodeResource::class, 'Промокоды'),
            ]),

            MenuGroup::make('Настройки', [
                MenuItem::make(SiteSettingsPage::class, 'Настройки сайта'),
                MenuItem::make(OnlineSettingsPage::class, 'Онлайн'),
                MenuItem::make(SettingResource::class, 'Все ключи'),
            ]),
            MenuItem::make(UtmMarkResource::class, 'UTM-метки'),
        ];
    }

    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);
    }

    protected function getFooterCopyright(): string
    {
        return \sprintf('&copy; %d GROWSKINS Admin', now()->year);
    }

    /**
     * @return array<string, string>
     */
    protected function getFooterMenu(): array
    {
        return [];
    }
}
