<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'provably', 'name' => 'Provably Fair', 'sort_order' => 0],
            ['slug' => 'upgrade', 'name' => 'Апгрейд', 'sort_order' => 1],
            ['slug' => 'deposit', 'name' => 'Пополнение', 'sort_order' => 2],
            ['slug' => 'withdraw', 'name' => 'Вывод', 'sort_order' => 3],
            ['slug' => 'account', 'name' => 'Аккаунт', 'sort_order' => 4],
            ['slug' => 'other', 'name' => 'Другое', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            FaqCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $items = [
            ['category' => 'provably', 'question' => 'Как работает Provably Fair?', 'answer' => 'Каждый апгрейд использует HMAC-SHA256 с уникальной парой сидов и nonce для генерации случайного числа. Результат можно проверить на странице верификации.'],
            ['category' => 'provably', 'question' => 'Как проверить результат апгрейда?', 'answer' => 'Перейдите на страницу верификации, введите клиентский сид, серверный сид и nonce. Система вычислит результат и покажет roll value.'],
            ['category' => 'provably', 'question' => 'Что такое серверный сид?', 'answer' => 'Случайная строка, генерируемая сервером. Её SHA-256 хэш показывается до игры, сам сид раскрывается после смены.'],
            ['category' => 'provably', 'question' => 'Могу ли я поменять клиентский сид?', 'answer' => 'Да, в любой момент на странице Provably Fair. При смене текущий серверный сид раскрывается и генерируется новая пара.'],
            ['category' => 'upgrade', 'question' => 'Как сделать апгрейд?', 'answer' => 'Выберите скин из инвентаря, выберите целевой скин дороже, нажмите GO.'],
            ['category' => 'upgrade', 'question' => 'Почему я не могу выбрать этот скин?', 'answer' => 'Шанс апгрейда должен быть от 1% до 95%. Если скин слишком дешёвый или дорогой — он недоступен.'],
            ['category' => 'upgrade', 'question' => 'Куда попадает выигранный скин?', 'answer' => 'В ваш инвентарь на сайте. Оттуда можно вывести или использовать в новом апгрейде.'],
            ['category' => 'deposit', 'question' => 'Какие способы пополнения доступны?', 'answer' => 'Карты (СБП, VISA/Mastercard), криптовалюта (USDT, TON, TRX), скины через Skinsback.'],
            ['category' => 'deposit', 'question' => 'Какая минимальная сумма пополнения?', 'answer' => '50₽ для карт, 2$ для крипты.'],
            ['category' => 'deposit', 'question' => 'Как долго зачисляется депозит?', 'answer' => 'Карты — моментально. Крипта — после подтверждения сети. Скины — до 8 дней из-за Steam Trade Protection.'],
            ['category' => 'withdraw', 'question' => 'Как вывести скин?', 'answer' => 'В профиле нажмите на скин и выберите «Вывести». Для вывода нужна Steam Trade URL.'],
            ['category' => 'withdraw', 'question' => 'Как долго идёт вывод?', 'answer' => 'Обычно до 15 минут. В редких случаях до 24 часов.'],
            ['category' => 'account', 'question' => 'Как войти на сайт?', 'answer' => 'Через Steam. Нажмите «Войти» и авторизуйтесь через свой Steam аккаунт.'],
            ['category' => 'account', 'question' => 'Где найти Trade URL?', 'answer' => 'В настройках Steam → Инвентарь → Конфиденциальность инвентаря → Trade URL.'],
            ['category' => 'other', 'question' => 'Есть ли промокоды?', 'answer' => 'Да, промокоды дают бонус к балансу или процент к пополнению. Вводите в профиле в блоке промокода.'],
            ['category' => 'other', 'question' => 'Как связаться с поддержкой?', 'answer' => 'Напишите нам в Telegram или Discord.'],
        ];

        foreach ($items as $i => $item) {
            $cat = FaqCategory::where('slug', $item['category'])->first();

            FaqItem::updateOrCreate(
                ['question' => $item['question']],
                [
                    'faq_category_id' => $cat?->id,
                    'answer' => $item['answer'],
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );
        }
    }
}
