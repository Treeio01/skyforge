import { Link, usePage } from "@inertiajs/react";
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon, SteamIcon } from "@/Components/UI/Icons";
import Button from "@/Components/UI/Button";
import { Chip, ChipLabel, Divider } from "./primitives";
import DepositBlock from "./sections/DepositBlock";
import LanguageMenu from "./sections/LanguageMenu";
import ProfileChip from "./sections/ProfileChip";
import SocialLinks from "./sections/SocialLinks";
import SoundMenu from "./sections/SoundMenu";
import { PageProps } from "@/types";

function Logo() {
    return (
        <Link href="/" prefetch="hover" className="1440:hidden 1340:flex hidden py-[3px] px-[3px] bg-accent w-full max-w-[187px]">
            <img src="/assets/img/logo.png" className="max-w-[56px]" alt="" />
        </Link>
    );
}

function StatsChips() {
    const { stats } = usePage<PageProps>().props;

    return (
        <div className="flex gap-[3px] items-stretch">
            <Chip>
                <GlobeIcon />
                <ChipLabel>{stats?.online?.toLocaleString('ru-RU') ?? '0'}</ChipLabel>
            </Chip>
            <Chip>
                <LevelsIcon />
                <ChipLabel>{stats?.total_upgrades?.toLocaleString('ru-RU') ?? '0'}</ChipLabel>
            </Chip>
            <Chip
                interactive
                className="bg-linear-to-r from-[#FE7A02] to-[#FE4D00]"
            >
                <BonusIcon />
                <ChipLabel>Бонусы</ChipLabel>
            </Chip>
        </div>
    );
}

function NavChips() {
    return (
        <div className="flex gap-[3px] items-stretch">
            <Link href="/market" prefetch="hover">
                <Chip interactive className="text-[#23262C] hover:text-white">
                    <MarketIcon />
                    <ChipLabel tone="inherit">Рынок Скинов</ChipLabel>
                </Chip>
            </Link>
            <Link href="/provably-fair" prefetch="hover">
                <Chip interactive className="text-[#23262C] hover:text-white">
                    <FaqIcon />
                    <ChipLabel tone="inherit">FAQ</ChipLabel>
                </Chip>
            </Link>
        </div>
    );
}

export default function Header() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    return (
        <header className="flex p-2.5 justify-between items-center w-full overflow-hidden">
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
                    <SoundMenu />
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
                        <span className="text-sm text-white font-sf-display font-medium leading-[120%]">Войти</span>
                    </Button>
                )}
            </div>
        </header>
    );
}
