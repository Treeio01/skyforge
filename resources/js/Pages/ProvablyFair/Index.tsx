import AppLayout from "@/Layouts/AppLayout";
import PageShell from "@/Components/Layout/PageShell";
import { FaqIcon } from "@/Components/UI/Icons";
import { usePage } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { PageProps } from "@/types";
import { AnimatePresence, motion } from "framer-motion";

interface FaqPageProps extends Record<string, unknown> {
    categories: Array<{ id: number; slug: string; name: string; name_en?: string | null }>;
    faq: Record<string, Array<{
        question: string;
        answer: string;
        question_en?: string | null;
        answer_en?: string | null;
    }>>;
}

const FALLBACK_CATEGORIES = [
    { id: "provably", label: "Provably Fair" },
    { id: "upgrade", label: "Апгрейд" },
    { id: "deposit", label: "Пополнение" },
    { id: "withdraw", label: "Вывод" },
    { id: "account", label: "Аккаунт" },
    { id: "other", label: "Другое" },
];

const FAQ_DATA: Record<string, Array<{ question: string; answer: string; question_en?: string | null; answer_en?: string | null }>> = {
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
            width="18"
            height="18"
            viewBox="0 0 20 20"
            fill="none"
            className="shrink-0"
            animate={{ rotate: open ? 180 : 0 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
        >
            <path
                d="M5 7.5L10 12.5L15 7.5"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
                className={open ? 'text-white' : 'text-white/55'}
            />
        </motion.svg>
    );
}

function AccordionItem({
    question,
    answer,
    index,
}: {
    question: string;
    answer: string;
    index: number;
}) {
    const [open, setOpen] = useState(false);

    return (
        <motion.div
            className={`rounded-[14px] overflow-hidden transition-colors duration-200 ${
                open
                    ? 'bg-[#1A2030]'
                    : 'bg-[#11161F] hover:bg-[#161C28]'
            }`}
            layout
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25, delay: Math.min(index * 0.04, 0.2) }}
        >
            <button
                onClick={() => setOpen(!open)}
                className="flex items-center justify-between w-full text-left cursor-pointer px-5 py-4 gap-4 group"
            >
                <span className={`font-sf-display text-[14px] leading-[140%] transition-colors duration-200 ${open ? 'text-white' : 'text-white/75 group-hover:text-white'}`}>
                    {question}
                </span>
                <div className={`flex items-center justify-center w-8 h-8 rounded-full shrink-0 transition-colors duration-200 ${open ? 'bg-white/14' : 'bg-white/6 group-hover:bg-white/10'}`}>
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
                        <div className="px-5 pb-5">
                            <div className="h-px bg-white/6 mb-4" />
                            <p className="text-white/65 font-sf-display font-light text-[13px] leading-[180%]">
                                {answer}
                            </p>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </motion.div>
    );
}

export default function ProvablyFairIndex() {
    const { t, i18n } = useTranslation();
    const { faq, categories: dbCategories } = usePage<PageProps<FaqPageProps>>().props;

    const isEn = i18n.language === 'en';

    const CATEGORIES = dbCategories && dbCategories.length > 0
        ? dbCategories.map((c) => ({
              id: c.slug,
              label: isEn && c.name_en ? c.name_en : c.name,
          }))
        : FALLBACK_CATEGORIES;

    const [category, setCategory] = useState(CATEGORIES[0]?.id ?? "provably");

    const hasFaqData = faq && Object.keys(faq).length > 0;
    const rawItems = hasFaqData ? (faq[category] ?? []) : (FAQ_DATA[category] ?? []);
    const items = rawItems.map((item) => ({
        question: isEn && item.question_en ? item.question_en : item.question,
        answer: isEn && item.answer_en ? item.answer_en : item.answer,
    }));
    const currentLabel = CATEGORIES.find((c) => c.id === category)?.label ?? t('faq.title');
    const subtitle = i18n.language === 'ru'
        ? `${items.length} ${items.length === 1 ? 'вопрос' : items.length < 5 ? 'вопроса' : 'вопросов'}`
        : `${items.length} ${items.length === 1 ? 'question' : 'questions'}`;

    return (
        <AppLayout>
            <PageShell icon={<FaqIcon />} title={t('faq.title')} subtitle={t('faq.subtitle')}>
                <div className="flex flex-1 min-h-0 gap-6 1024:gap-8">
                    {/* Сайдбар категорий */}
                    <div className="hidden 1024:flex flex-col w-[240px] shrink-0 gap-1 self-start sticky top-6 p-2 rounded-[14px] bg-[#0E131C]">
                        <span className="text-white/35 font-sf-display text-[11px] uppercase tracking-[0.08em] px-3 pt-1.5 pb-2">
                            {t('faq.sections')}
                        </span>
                        {CATEGORIES.map((cat) => {
                            const isActive = category === cat.id;
                            return (
                                <button
                                    key={cat.id}
                                    onClick={() => setCategory(cat.id)}
                                    className={`flex items-center justify-between gap-2 px-3 py-2.5 rounded-[10px] cursor-pointer transition-colors duration-200 font-sf-display text-[13px] leading-[120%] ${
                                        isActive
                                            ? 'bg-[#1A2030] text-white'
                                            : 'text-white/55 hover:bg-white/4 hover:text-white'
                                    }`}
                                >
                                    <span>{cat.label}</span>
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 16 16"
                                        fill="none"
                                        className={`shrink-0 transition-opacity duration-200 ${isActive ? 'opacity-100 text-white/70' : 'opacity-30 text-white/40'}`}
                                    >
                                        <path d="M6 4L10 8L6 12" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            );
                        })}
                    </div>

                    {/* Контент */}
                    <div className="flex flex-col flex-1 min-w-0 gap-4">
                        <div className="flex items-baseline gap-2 1024:hidden">
                            <span className="text-white font-gotham font-medium text-lg leading-[110%]">{currentLabel}</span>
                            <span className="text-white/35 font-sf-display text-[12px]">{subtitle}</span>
                        </div>

                        {/* Мобильные табы */}
                        <div className="flex 1024:hidden gap-1.5 overflow-x-auto skins-scroll pb-1 -mx-1 px-1">
                            {CATEGORIES.map((cat) => {
                                const isActive = category === cat.id;
                                return (
                                    <button
                                        key={cat.id}
                                        onClick={() => setCategory(cat.id)}
                                        className={`shrink-0 py-2 px-3.5 rounded-[10px] cursor-pointer transition-colors duration-200 font-sf-display text-[12px] leading-[120%] ${
                                            isActive
                                                ? 'bg-[#1A2030] text-white'
                                                : 'bg-[#11161F] text-white/55 hover:text-white hover:bg-[#141A24]'
                                        }`}
                                    >
                                        {cat.label}
                                    </button>
                                );
                            })}
                        </div>

                        {/* Контент-блок с заголовком раздела и списком */}
                        <div className="flex flex-col gap-3 p-4 1024:p-6 rounded-[14px] bg-[#0E131C]">
                            <div className="flex items-baseline gap-2">
                                <span className="text-white font-gotham font-medium text-lg 1024:text-xl leading-[110%]">{currentLabel}</span>
                                <span className="text-white/35 font-sf-display text-[12px]">{subtitle}</span>
                            </div>

                            <div className="flex flex-col gap-2">
                                {items.map((item, i) => (
                                    <AccordionItem
                                        key={`${category}-${i}`}
                                        question={item.question}
                                        answer={item.answer}
                                        index={i}
                                    />
                                ))}
                                {items.length === 0 && (
                                    <div className="flex items-center justify-center py-12">
                                        <span className="text-white/30 font-sf-display text-[13px]">{t('faq.empty')}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </PageShell>
        </AppLayout>
    );
}
