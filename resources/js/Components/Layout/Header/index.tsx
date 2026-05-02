import { Link, usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon, SteamIcon } from "@/Components/UI/Icons";
import Button from "@/Components/UI/Button";
import { Chip, ChipLabel, Divider } from "./primitives";
import DepositBlock from "./sections/DepositBlock";
import LanguageMenu from "./sections/LanguageMenu";
import ProfileChip from "./sections/ProfileChip";
import SocialLinks from "./sections/SocialLinks";
import { PageProps } from "@/types";
import { useOnlineCount } from "@/hooks/useOnlineCount";
import { useUpgradeCount } from "@/hooks/useUpgradeCount";

function Logo() {
    return (
        <Link
            href="/"
            prefetch="hover"
            className="flex py-[6px] px-[8px] rounded-[10px] shrink-0 transition-opacity duration-150 hover:opacity-80 1340:hidden"
        >
            <img src="/assets/img/logo.png" className="max-w-[56px]" alt="" />
        </Link>
    );
}

function StatsChips() {
    const { t } = useTranslation();
    const onlineDisplay = useOnlineCount();
    const totalUpgrades = useUpgradeCount();

    return (
        <div className="flex gap-[3px] items-stretch">
            <Chip className="bg-chip text-white">
                <GlobeIcon />
                <ChipLabel>{onlineDisplay.toLocaleString('ru-RU')}</ChipLabel>
            </Chip>
            <Chip className="bg-chip text-white">
                <LevelsIcon />
                <ChipLabel>{totalUpgrades.toLocaleString('ru-RU')}</ChipLabel>
            </Chip>
            <Chip
                interactive
                className="bg-linear-to-r from-[#FE7A02] to-[#FE4D00] text-white"
            >
                <BonusIcon />
                <ChipLabel>{t('header.bonuses')}</ChipLabel>
            </Chip>
        </div>
    );
}

function NavChip({ href, icon: Icon, label, className = '' }: { href: string; icon: React.FC; label: string; className?: string }) {
    const url = usePage().url;
    const isActive = url === href || url.startsWith(`${href}/`);
    const stateClass = isActive
        ? 'bg-chip text-white'
        : 'bg-transparent text-white/85 hover:bg-chip hover:text-white active:opacity-80';
    return (
        <Link href={href} prefetch="hover" className={className}>
            <Chip
                interactive
                className={`${stateClass} transition-colors duration-150`}
            >
                <Icon />
                <ChipLabel tone="inherit">{label}</ChipLabel>
            </Chip>
        </Link>
    );
}

function NavChips() {
    const { t } = useTranslation();
    return (
        <div className="flex gap-[3px] items-stretch">
            <NavChip href="/market" icon={MarketIcon} label={t('header.market')} />
            <NavChip href="/provably-fair" icon={FaqIcon} label={t('header.faq')} className="hidden md:flex" />
        </div>
    );
}

export default function Header() {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    return (
        <header className="relative z-[400] flex p-2.5 justify-between items-center w-full overflow-x-clip bg-accent">
            <Logo />
            <div className="flex items-center gap-3 min-w-0 flex-shrink">
                <div className="hidden wide:flex items-center gap-3">
                    <StatsChips />
                    <Divider />
                </div>
                <NavChips />
            </div>
            <div className="flex items-center md:gap-3 gap-2 shrink-0">
                <div className="hidden wide:flex gap-1 items-stretch">
                    <SocialLinks />
                    <LanguageMenu />
                </div>
                <Divider className="hidden wide:flex" />
                {user ? (
                    <>
                        <DepositBlock />
                        <ProfileChip />
                    </>
                ) : (
                    <Button
                        variant="primary"
                        onClick={() => window.dispatchEvent(new Event('show-login-modal'))}
                    >
                        <SteamIcon />
                        <span className="text-sm text-white font-sf-display font-medium leading-[120%]">{t('common.login')}</span>
                    </Button>
                )}
            </div>
        </header>
    );
}
