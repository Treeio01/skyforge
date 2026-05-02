<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User\Pages;

use App\Models\User;
use App\MoonShine\Resources\User\UserResource;
use App\Services\Admin\UserRtpService;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<UserResource>
 */
class UserDetailPage extends DetailPage
{
    /** @var array<int, array<string, mixed>> */
    private static array $rtpMemo = [];

    /**
     * @return array<string, mixed>
     */
    private function rtp(User $user): array
    {
        return self::$rtpMemo[$user->id] ??= app(UserRtpService::class)->forUser($user);
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Preview::make('Аватар', 'avatar_url')->image(),
            Text::make('Никнейм', 'username'),
            Text::make('Steam ID', 'steam_id'),
            Text::make('Trade URL', 'trade_url'),
            Number::make('Баланс', 'balance')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Пополнено', 'total_deposited')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Выведено', 'total_withdrawn')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Апгрейдов на сумму', 'total_upgraded')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Выиграно', 'total_won')->modifyRawValue(MoneyFormatter::field()),
            Text::make('UTM', formatted: fn ($item) => $item->utmMark?->slug ?? '—'),
            Switcher::make('Забанен', 'is_banned'),
            Textarea::make('Причина бана', 'ban_reason'),
            Switcher::make('Админ', 'is_admin'),
            Number::make('Край казино (%)', 'house_edge_override'),
            Number::make('Модификатор шанса', 'chance_modifier'),

            // RTP block
            Text::make('Апгрейдов всего', formatted: fn ($item) => (string) $this->rtp($item)['count_all']),
            Text::make('Из них побед', formatted: function ($item) {
                $rtp = $this->rtp($item);
                $rate = $rtp['count_all'] > 0 ? round($rtp['wins_all'] / $rtp['count_all'] * 100, 1) : 0;

                return "{$rtp['wins_all']} ({$rate}%)";
            }),
            Text::make('Поставлено всего', formatted: fn ($item) => MoneyFormatter::format($this->rtp($item)['bet_all'])),
            Text::make('Выиграно всего', formatted: fn ($item) => MoneyFormatter::format($this->rtp($item)['won_all'])),
            Text::make('RTP за всё время', formatted: function ($item) {
                $rtp = $this->rtp($item)['rtp_all'];

                return $rtp === null ? '—' : "{$rtp}%";
            }),

            Text::make('Апгрейдов за 30д', formatted: fn ($item) => (string) $this->rtp($item)['count_30d']),
            Text::make('Поставлено за 30д', formatted: fn ($item) => MoneyFormatter::format($this->rtp($item)['bet_30d'])),
            Text::make('Выиграно за 30д', formatted: fn ($item) => MoneyFormatter::format($this->rtp($item)['won_30d'])),
            Text::make('RTP за 30д', formatted: function ($item) {
                $rtp = $this->rtp($item)['rtp_30d'];

                return $rtp === null ? '—' : "{$rtp}%";
            }),

            Date::make('Активность', 'last_active_at'),
            Date::make('Регистрация', 'created_at'),
        ];
    }
}
