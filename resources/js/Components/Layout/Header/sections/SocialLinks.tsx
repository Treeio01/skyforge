import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import {
    DiscordIcon,
    TelegramIcon,
    TiktokIcon,
    TwitchIcon,
    VkIcon,
    YoutubeIcon,
} from '../icons';

const SOCIAL_ICONS: Record<string, React.FC> = {
    vk: VkIcon,
    telegram: TelegramIcon,
    discord: DiscordIcon,
    tiktok: TiktokIcon,
    youtube: YoutubeIcon,
    twitch: TwitchIcon,
};

export default function SocialLinks() {
    const { socials } = usePage<PageProps>().props;

    const links = socials
        ? Object.entries(socials).filter(([, url]) => url).map(([key, url]) => ({
            href: url,
            icon: SOCIAL_ICONS[key],
        })).filter((l) => l.icon)
        : [];

    return (
        <div className="flex p-3 gap-[13px] items-center [&_a]:text-[#23262C] [&_a]:transition-colors [&_a]:duration-150 [&_a:hover]:text-white [&_a]:cursor-pointer">
            {links.map(({ href, icon: Icon }) => (
                <a key={href} href={href} target="_blank" rel="noopener noreferrer">
                    <Icon />
                </a>
            ))}
        </div>
    );
}
