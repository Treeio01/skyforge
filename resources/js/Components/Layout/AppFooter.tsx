import { Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import {
    DiscordIcon,
    TelegramIcon,
    TiktokIcon,
    TwitchIcon,
    VkIcon,
    YoutubeIcon,
} from '@/Components/UI/Icons';

const SOCIAL_ICONS: Record<string, React.FC> = {
    vk: VkIcon,
    telegram: TelegramIcon,
    discord: DiscordIcon,
    tiktok: TiktokIcon,
    youtube: YoutubeIcon,
    twitch: TwitchIcon,
};

const linkCls =
    'text-white/35 font-sf-display text-[13px] hover:text-white/70 transition-colors duration-150';
const headingCls =
    'text-white/60 font-sf-display text-[11px] uppercase tracking-[0.08em]';

export default function AppFooter() {
    const { socials } = usePage<PageProps>().props;

    const socialLinks = socials
        ? Object.entries(socials)
              .filter(([, url]) => url)
              .map(([key, url]) => ({ href: url, icon: SOCIAL_ICONS[key], key }))
              .filter((l) => l.icon)
        : [];

    return (
        <footer className="bg-[#151B27] border-t border-white/6 px-4 1024:px-8 pt-10">
            <div className="grid grid-cols-2 1024:grid-cols-4 gap-8 pt-10 pb-8">
                {/* Бренд */}
                <div className="col-span-2 1024:col-span-1 flex flex-col gap-3">
                    <Link href="/" className="inline-block">
                        <img
                            src="/assets/img/logo.png"
                            alt="GrowSkins"
                            className="h-8 w-auto object-contain object-left"
                        />
                    </Link>
                    <p className="text-white/30 font-sf-display text-[12px] leading-[180%] max-w-[220px]">
                        Честный апгрейд скинов CS2 с открытым алгоритмом Provably Fair.
                    </p>
                    {socialLinks.length > 0 && (
                        <div className="flex items-center gap-2 mt-1">
                            {socialLinks.map(({ href, icon: Icon, key }) => (
                                <a
                                    key={key}
                                    href={href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center justify-center w-9 h-9 rounded-[10px] bg-white/5 hover:bg-white/12 active:bg-white/15 text-white/55 hover:text-white transition-colors duration-150"
                                    title={key}
                                >
                                    <Icon />
                                </a>
                            ))}
                        </div>
                    )}
                </div>

                {/* Игры */}
                <div className="flex flex-col gap-3">
                    <span className={headingCls}>Игры</span>
                    <div className="flex flex-col gap-2.5">
                        <Link href="/" className={linkCls} prefetch="hover">Апгрейд</Link>
                        <Link href="/market" className={linkCls} prefetch="hover">Маркет</Link>
                        <Link href="/provably-fair" className={linkCls} prefetch="hover">Provably Fair</Link>
                    </div>
                </div>

                {/* Поддержка */}
                <div className="flex flex-col gap-3">
                    <span className={headingCls}>Поддержка</span>
                    <div className="flex flex-col gap-2.5">
                        <Link href="/provably-fair" className={linkCls} prefetch="hover">FAQ</Link>
                        {socials?.telegram && (
                            <a
                                href={socials.telegram}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={linkCls}
                            >
                                Telegram
                            </a>
                        )}
                        {socials?.discord && (
                            <a
                                href={socials.discord}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={linkCls}
                            >
                                Discord
                            </a>
                        )}
                        <a href="mailto:support@growskins.ru" className={linkCls}>
                            Обратная связь
                        </a>
                    </div>
                </div>

                {/* Правовое */}
                <div className="flex flex-col gap-3">
                    <span className={headingCls}>Правовое</span>
                    <div className="flex flex-col gap-2.5">
                        <a href="/terms" className={linkCls}>Пользовательское соглашение</a>
                        <a href="/privacy" className={linkCls}>Политика конфиденциальности</a>
                        <a href="/responsible-gaming" className={linkCls}>Ответственная игра</a>
                    </div>
                </div>
            </div>

            {/* Нижняя строка */}
            <div className="flex flex-col xs:flex-row items-center justify-between gap-3 py-5 border-t border-white/4">
                <span className="text-white/20 font-sf-display text-[12px]">© 2026 GrowSkins. Все права защищены.</span>
                <span className="text-white/15 font-sf-display text-[11px] text-center">
                    Сайт не связан с Valve Corporation. CS2 — товарный знак Valve.
                </span>
            </div>
        </footer>
    );
}
