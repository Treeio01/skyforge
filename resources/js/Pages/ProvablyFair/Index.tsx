import AppLayout from "@/Layouts/AppLayout";
import { usePage } from "@inertiajs/react";
import { useState } from "react";
import { PageProps } from "@/types";
import { AnimatePresence, motion } from "framer-motion";

interface FaqPageProps extends Record<string, unknown> {
    categories: Array<{ id: number; slug: string; name: string }>;
    faq: Record<string, Array<{ question: string; answer: string }>>;
}

const FALLBACK_CATEGORIES = [
    { id: "provably", label: "Provably Fair" },
    { id: "upgrade", label: "Апгрейд" },
    { id: "deposit", label: "Пополнение" },
    { id: "withdraw", label: "Вывод" },
    { id: "account", label: "Аккаунт" },
    { id: "other", label: "Другое" },
];

const FAQ_DATA: Record<string, Array<{ question: string; answer: string }>> = {
    provably: [
        {
            question:
                "На каждом апгрейде мы используем технологию Provably Fair?",
            answer: "Да, каждый апгрейд использует HMAC-SHA256 с уникальной парой сидов и nonce для генерации случайного числа.",
        },
        {
            question: "Как проверить результат апгрейда?",
            answer: "Перейдите на страницу верификации апгрейда. Введите клиентский сид, серверный сид и nonce. Система вычислит результат.",
        },
        {
            question: "Что такое серверный сид?",
            answer: "Случайная строка, генерируемая сервером. Её хэш показывается до игры, сам сид раскрывается после.",
        },
        {
            question: "Что такое клиентский сид?",
            answer: "Строка, которую вы устанавливаете сами. Участвует в генерации результата.",
        },
        {
            question: "Что такое Nonce?",
            answer: "Порядковый номер игры в текущей паре сидов. Увеличивается на 1 с каждым апгрейдом.",
        },
        {
            question: "Могу ли я поменять клиентский сид?",
            answer: "Да, в любой момент. При смене текущий серверный сид раскрывается и генерируется новая пара.",
        },
        {
            question: "Как работает формула расчёта шанса?",
            answer: "Шанс = (ставка / цель) × 0.95 × 100%. Минимум 1%, максимум 95%. Комиссия 5%.",
        },
    ],
    upgrade: [
        {
            question: "Как сделать апгрейд?",
            answer: "Выберите скин из инвентаря, выберите целевой скин дороже, нажмите GO.",
        },
        {
            question: "Почему я не могу выбрать этот скин?",
            answer: "Шанс апгрейда должен быть от 1% до 95%. Если скин слишком дешёвый или дорогой — он недоступен.",
        },
        {
            question: "Куда попадает выигранный скин?",
            answer: "В ваш инвентарь на сайте. Оттуда можно вывести или использовать в новом апгрейде.",
        },
    ],
    deposit: [
        {
            question: "Какие способы пополнения доступны?",
            answer: "Карты (СБП, VISA), криптовалюта (USDT, TON, TRX), скины.",
        },
        {
            question: "Какая минимальная сумма пополнения?",
            answer: "50₽ для карт, 2$ для крипты.",
        },
        {
            question: "Как долго зачисляется депозит?",
            answer: "Карты — моментально. Крипта — после подтверждения сети. Скины — до 8 дней.",
        },
    ],
    withdraw: [
        {
            question: "Как вывести скин?",
            answer: "В профиле нажмите на скин и выберите «Вывести». Нужна Trade URL.",
        },
        {
            question: "Как долго идёт вывод?",
            answer: "Обычно до 15 минут. В редких случаях до 24 часов.",
        },
    ],
    account: [
        {
            question: "Как войти на сайт?",
            answer: "Через Steam. Нажмите «Войти» и авторизуйтесь через Steam.",
        },
        {
            question: "Где найти Trade URL?",
            answer: "В настройках Steam → Инвентарь → Конфиденциальность → Trade URL.",
        },
    ],
    other: [
        {
            question: "Есть ли промокоды?",
            answer: "Да, промокоды дают бонус к балансу или % к пополнению. Вводите в профиле.",
        },
        {
            question: "Как связаться с поддержкой?",
            answer: "Напишите нам в Telegram или через форму на сайте.",
        },
    ],
};

function ChevronIcon({ open }: { open: boolean }) {
    return (
        <motion.svg
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            className="shrink-0"
            animate={{ rotate: open ? 180 : 0 }}
            transition={{ duration: 0.2 }}
        >
            <path
                d="M5 7.5L10 12.5L15 7.5"
                stroke="white"
                strokeOpacity="0.7"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </motion.svg>
    );
}

function AccordionItem({
    question,
    answer,
}: {
    question: string;
    answer: string;
}) {
    const [open, setOpen] = useState(false);

    return (
        <motion.div
            className={`rounded-[16px] overflow-hidden transition-colors duration-200 ${
                open ? 'bg-white/4' : 'bg-white/2 hover:bg-white/3'
            }`}
            layout
        >
            <button
                onClick={() => setOpen(!open)}
                className="flex items-center justify-between w-full text-left cursor-pointer px-5 py-4 gap-4"
            >
                <span className={`font-sf-display text-[14px] leading-[140%] transition-colors ${open ? 'text-white' : 'text-white/70'}`}>
                    {question}
                </span>
                <div className={`flex items-center justify-center w-7 h-7 rounded-full shrink-0 transition-colors ${open ? 'bg-brand/20' : 'bg-white/6'}`}>
                    <ChevronIcon open={open} />
                </div>
            </button>
            <AnimatePresence initial={false}>
                {open && (
                    <motion.div
                        key="content"
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: "auto", opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.22, ease: "easeInOut" }}
                        className="overflow-hidden"
                    >
                        <p className="text-white/50 font-sf-display font-light text-[13px] leading-[190%] px-5 pb-5">
                            {answer}
                        </p>
                    </motion.div>
                )}
            </AnimatePresence>
        </motion.div>
    );
}

export default function ProvablyFairIndex() {
    const { faq, categories: dbCategories } = usePage<PageProps<FaqPageProps>>().props;

    const CATEGORIES = dbCategories && dbCategories.length > 0
        ? dbCategories.map((c) => ({ id: c.slug, label: c.name }))
        : FALLBACK_CATEGORIES;

    const [category, setCategory] = useState(CATEGORIES[0]?.id ?? "provably");

    const hasFaqData = faq && Object.keys(faq).length > 0;
    const items = hasFaqData ? (faq[category] ?? []) : (FAQ_DATA[category] ?? []);
    return (
        <AppLayout>
            <div className="flex flex-1 w-full px-4 1024:px-8 py-6 gap-8">
                {/* Сайдбар категорий */}
                <div className="hidden 1024:flex flex-col w-[220px] shrink-0 gap-1 self-start sticky top-6">
                    <span className="text-white/30 font-sf-display text-[11px] uppercase tracking-[0.1em] px-3 mb-2">Разделы</span>
                    {CATEGORIES.map((cat) => (
                        <button
                            key={cat.id}
                            onClick={() => setCategory(cat.id)}
                            className={`flex items-center justify-between gap-2 px-3 py-2.5 rounded-[10px] cursor-pointer transition-all font-sf-display text-[13px] leading-[120%] ${
                                category === cat.id
                                    ? 'bg-white/8 text-white'
                                    : 'text-white/40 hover:text-white/65 hover:bg-white/4'
                            }`}
                        >
                            <span>{cat.label}</span>
                            {category === cat.id && (
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" className="shrink-0 text-white/40">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            )}
                        </button>
                    ))}
                </div>

                {/* Контент */}
                <div className="flex flex-col flex-1 min-w-0 gap-4">
                    {/* Заголовок */}
                    <div className="flex flex-col gap-1 mb-2">
                        <h1 className="text-white font-gotham font-medium text-2xl leading-[110%]">
                            {CATEGORIES.find(c => c.id === category)?.label ?? 'FAQ'}
                        </h1>
                        <span className="text-white/35 font-sf-display text-[13px]">
                            {items.length} {items.length === 1 ? 'вопрос' : items.length < 5 ? 'вопроса' : 'вопросов'}
                        </span>
                    </div>

                    {/* Мобильные табы */}
                    <div className="flex 1024:hidden gap-1.5 overflow-x-auto skins-scroll pb-1">
                        {CATEGORIES.map((cat) => (
                            <button
                                key={cat.id}
                                onClick={() => setCategory(cat.id)}
                                className={`shrink-0 py-2 px-3.5 rounded-[10px] cursor-pointer transition-all font-sf-display text-[12px] leading-[120%] ${
                                    category === cat.id
                                        ? 'bg-white/10 text-white'
                                        : 'text-white/35 bg-white/4 hover:bg-white/7'
                                }`}
                            >
                                {cat.label}
                            </button>
                        ))}
                    </div>

                    {/* Список аккордеонов */}
                    <div className="flex flex-col gap-2">
                        {items.map((item, i) => (
                            <AccordionItem
                                key={`${category}-${i}`}
                                question={item.question}
                                answer={item.answer}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
