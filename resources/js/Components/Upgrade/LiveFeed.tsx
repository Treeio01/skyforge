import { mapRarityColor, parseSkinName } from '@/utils/skinHelpers';
import { FeedItem } from '@/types';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import { AnimatePresence } from 'framer-motion';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useToast } from '@/Components/UI/Toast';
import LiveFeedItem, { SkinRarity } from './LiveFeedItem';

export type LiveFeedEntry = {
    id: string | number;
    rarity: SkinRarity;
    weapon: string;
    name: string;
    image: string;
};

const MAX_FEED_ITEMS = 20;

function feedItemToEntry(item: FeedItem): LiveFeedEntry {
    const { weapon, name } = parseSkinName(item.target_skin_name);

    return {
        id: `feed-${item.id}`,
        rarity: mapRarityColor(item.rarity_color),
        weapon,
        name,
        image: item.target_skin_image || '',
    };
}

type FeedTab = 'all' | 'top';
const TOP_RARITIES: SkinRarity[] = ['class', 'covrt', 'contra', 'extra'];

interface FeedTabButtonProps {
    active: boolean;
    onClick: () => void;
    label: string;
    icon: React.ReactNode;
}

function FeedTabButton({ active, onClick, label, icon }: FeedTabButtonProps) {
    const stateClass = active
        ? 'bg-chip text-white'
        : 'bg-accent text-white/40 hover:bg-white/5 hover:text-white/80 active:bg-white/10';
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex w-full py-[17px] px-[22px] gap-[6px] items-center justify-center rounded-[10px] cursor-pointer transition-colors duration-150 ${stateClass}`}
        >
            <span className="text-[13px] leading-[104%] font-sf-display">{label}</span>
            <span className="flex p-0.5">{icon}</span>
        </button>
    );
}

export default function LiveFeed() {
    const { t } = useTranslation();
    const [items, setItems] = useState<LiveFeedEntry[]>([]);
    const [tab, setTab] = useState<FeedTab>('all');
    const { toast } = useToast();

    // Загрузить последние апгрейды при монтировании
    useEffect(() => {
        const controller = new AbortController();
        axios
            .get<{ data: FeedItem[] }>('/api/live-feed', { signal: controller.signal })
            .then((res) => {
                const entries = (res.data.data || []).map(feedItemToEntry);
                setItems(entries.slice(0, MAX_FEED_ITEMS));
            })
            .catch((err) => {
                if (!axios.isCancel(err)) {
                    toast('error', t('feed.load_error'));
                }
            });
        return () => controller.abort();
    }, []);

    // WebSocket: слушаем канал 'upgrades' через Echo
    useEffect(() => {
        if (!window.Echo) return;

        const channel = window.Echo.channel('upgrades');

        channel.listen('.UpgradeCompleted', (event: FeedItem) => {
            const entry = feedItemToEntry(event);
            setItems((prev) => [
                entry,
                ...prev.filter((item) => item.id !== entry.id),
            ].slice(0, MAX_FEED_ITEMS));
        });

        return () => {
            window.Echo.leaveChannel('upgrades');
        };
    }, []);

    const visibleItems = tab === 'top' ? items.filter((i) => TOP_RARITIES.includes(i.rarity)) : items;

    return (
        <div className="hidden 1340:flex flex-col max-w-[187px] h-screen sticky top-0 self-start bg-accent">
            <Link href="/" className="flex py-[10px] px-[10px] rounded-[12px] transition-opacity duration-150 hover:opacity-80">
                <img
                    src="/assets/img/logo.png"
                    className="max-w-[56px]"
                    alt=""
                />
            </Link>
            <div className="flex py-[3px] gap-[3px]">
                <FeedTabButton
                    active={tab === 'all'}
                    onClick={() => setTab('all')}
                    label={t('feed_tabs.all')}
                    icon={
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.25 3.5H3.75C3.61739 3.5 3.49021 3.55268 3.39645 3.64645C3.30268 3.74021 3.25 3.86739 3.25 4V6.5C3.25 6.63261 3.30268 6.75979 3.39645 6.85355C3.49021 6.94732 3.61739 7 3.75 7H6.25C6.38261 7 6.50979 6.94732 6.60355 6.85355C6.69732 6.75979 6.75 6.63261 6.75 6.5V4C6.75 3.86739 6.69732 3.74021 6.60355 3.64645C6.50979 3.55268 6.38261 3.5 6.25 3.5ZM6.25 9H3.75C3.61739 9 3.49021 9.05268 3.39645 9.14645C3.30268 9.24021 3.25 9.36739 3.25 9.5V12C3.25 12.1326 3.30268 12.2598 3.39645 12.3536C3.49021 12.4473 3.61739 12.5 3.75 12.5H6.25C6.38261 12.5 6.50979 12.4473 6.60355 12.3536C6.69732 12.2598 6.75 12.1326 6.75 12V9.5C6.75 9.36739 6.69732 9.24021 6.60355 9.14645C6.50979 9.05268 6.38261 9 6.25 9ZM11.75 3.5H9.25C9.11739 3.5 8.99021 3.55268 8.89645 3.64645C8.80268 3.74021 8.75 3.86739 8.75 4V6.5C8.75 6.63261 8.80268 6.75979 8.89645 6.85355C8.99021 6.94732 9.11739 7 9.25 7H11.75C11.8826 7 12.0098 6.94732 12.1036 6.85355C12.1973 6.75979 12.25 6.63261 12.25 6.5V4C12.25 3.86739 12.1973 3.74021 12.1036 3.64645C12.0098 3.55268 11.8826 3.5 11.75 3.5ZM11.75 9H9.25C9.11739 9 8.99021 9.05268 8.89645 9.14645C8.80268 9.24021 8.75 9.36739 8.75 9.5V12C8.75 12.1326 8.80268 12.2598 8.89645 12.3536C8.99021 12.4473 9.11739 12.5 9.25 12.5H11.75C11.8826 12.5 12.0098 12.4473 12.1036 12.3536C12.1973 12.2598 12.25 12.1326 12.25 12V9.5C12.25 9.36739 12.1973 9.24021 12.1036 9.14645C12.0098 9.05268 11.8826 9 11.75 9Z"
                                fill="currentColor"
                                stroke="currentColor"
                                strokeLinejoin="round"
                            />
                        </svg>
                    }
                />
                <FeedTabButton
                    active={tab === 'top'}
                    onClick={() => setTab('top')}
                    label={t('feed_tabs.top')}
                    icon={
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 12 12" fill="none">
                            <path
                                d="M10.5469 2.34375H9V1.875C9 1.66875 8.83125 1.5 8.625 1.5H3.375C3.16875 1.5 3 1.66875 3 1.875V2.34375H1.45312C1.27266 2.34375 1.125 2.49141 1.125 2.67188C1.125 3.63984 1.30781 4.21875 1.65469 4.79062C1.97344 5.31562 2.42578 5.63906 2.97422 5.73516C3.03984 5.74687 3.09375 5.78906 3.11953 5.85C3.26484 6.21094 3.59297 6.66562 4.31719 7.07344C4.79062 7.34062 5.18203 7.50234 5.52891 7.57969C5.61328 7.59844 5.67422 7.67578 5.67422 7.7625V9.65625C5.67422 9.75937 5.58984 9.84375 5.48672 9.84375H3.94922C3.77344 9.84375 3.62109 9.97969 3.61172 10.1555C3.60234 10.343 3.75234 10.5 3.93984 10.5H8.05547C8.23125 10.5 8.38359 10.3641 8.39297 10.1883C8.40234 10.0008 8.25234 9.84375 8.06484 9.84375H6.51797C6.41484 9.84375 6.33047 9.75937 6.33047 9.65625V7.76484C6.33047 7.67812 6.39141 7.60078 6.47578 7.58203C6.82031 7.50469 7.21406 7.34062 7.6875 7.07578C8.41172 6.66797 8.73984 6.21328 8.88516 5.85234C8.91094 5.79141 8.96484 5.74688 9.03047 5.7375C9.57891 5.64141 10.0312 5.31797 10.35 4.79297C10.6922 4.21875 10.875 3.63984 10.875 2.67188C10.875 2.49141 10.7273 2.34375 10.5469 2.34375ZM3 4.93594C3 5.00156 2.93437 5.04844 2.87344 5.025C2.57344 4.91016 2.32969 4.68047 2.15625 4.34766C2.02031 4.08984 1.86094 3.84609 1.80234 3.20391C1.79297 3.09375 1.87969 3 1.98984 3H2.8125C2.91562 3 3 3.08438 3 3.1875V4.93594ZM9.84375 4.34766C9.67031 4.68047 9.42656 4.91016 9.12656 5.025C9.06562 5.04844 9 5.00156 9 4.93594V3.1875C9 3.08438 9.08437 3 9.1875 3H10.0102C10.1203 3 10.207 3.09375 10.1977 3.20391C10.1391 3.84609 9.97734 4.08984 9.84375 4.34766Z"
                                fill="currentColor"
                            />
                        </svg>
                    }
                />
            </div>
            <div className="flex flex-col gap-[3px] flex-1 min-h-0 overflow-hidden">
                <AnimatePresence initial={false}>
                    {visibleItems.map((item) => (
                        <LiveFeedItem
                            key={item.id}
                            item={item}
                        />
                    ))}
                </AnimatePresence>
            </div>
        </div>
    );
}
