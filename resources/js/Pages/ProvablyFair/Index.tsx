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
                strokeOpacity="0.3"
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
        <button
            onClick={() => setOpen(!open)}
            className="flex flex-col gap-5 w-full text-left cursor-pointer px-6 py-5"
        >
            <div className="flex items-center justify-between w-full">
                <span className="text-white font-sf-display text-[14px] leading-[104%]">
                    {question}
                </span>
                <ChevronIcon open={open} />
            </div>
            <AnimatePresence initial={false}>
                {open && (
                    <motion.div
                        key="content"
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: "auto", opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.25, ease: "easeInOut" }}
                        className="overflow-hidden w-full"
                    >
                        <p className="text-white font-sf-display font-light text-[13px] leading-[184%]">
                            {answer}
                        </p>
                    </motion.div>
                )}
            </AnimatePresence>
        </button>
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
    const activeLabel = CATEGORIES.find((c) => c.id === category)?.label ?? "";

    return (
        <AppLayout>
            <div className="flex flex-1 w-full gap-6">
                {/* Сайдбар категорий */}
                <div className="hidden 1024:flex flex-col w-[200px] shrink-0 p-4 gap-1">
                    {CATEGORIES.map((cat) => (
                        <button
                            key={cat.id}
                            onClick={() => setCategory(cat.id)}
                            className={`flex flex-col pb-5.25 border-b border-[#2A2E35] items-center cursor-pointer transition-colors  ${
                                category === cat.id
                                    ? "text-white"
                                    : "text-white/30 hover:text-white/50"
                            }`}
                        >
                            <div className="flex gap-[5px] font-sf-display text-[13px] leading-[120%] text-left w-full justify-between items-center">
                                <span>{cat.label}</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" className="shrink-0">
                                    <path d="M6 4L10 8L6 12" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            </div>
                        </button>
                    ))}
                </div>

                {/* Контент */}
                <div className="flex flex-col flex-1">
                    {/* Мобильные табы */}
                    <div className="flex 1024:hidden gap-1 overflow-x-auto skins-scroll pb-3 mb-4">
                        {CATEGORIES.map((cat) => (
                            <button
                                key={cat.id}
                                onClick={() => setCategory(cat.id)}
                                className={`shrink-0 py-2 px-3 rounded-[8px] cursor-pointer transition-colors font-sf-display text-[12px] leading-[120%] ${
                                    category === cat.id
                                        ? "bg-white/5 text-white"
                                        : "text-white/30"
                                }`}
                            >
                                {cat.label}
                            </button>
                        ))}
                    </div>

                    <div className="flex flex-col">
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
