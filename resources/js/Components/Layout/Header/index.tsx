import { Link, usePage } from "@inertiajs/react";
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon, SteamIcon } from "@/Components/UI/Icons";
import { Chip, ChipLabel, Divider } from "./primitives";
import DepositBlock from "./sections/DepositBlock";
import LanguageMenu from "./sections/LanguageMenu";
import ProfileChip from "./sections/ProfileChip";
import SocialLinks from "./sections/SocialLinks";
import SoundMenu from "./sections/SoundMenu";
import { PageProps } from "@/types";

function Logo() {
    return (
        <Link href="/" className="1440:hidden 1340:flex hidden py-[3px] px-[3px] bg-accent w-full max-w-[187px]">
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
            <Link href="/market">
                <Chip interactive className="text-[#23262C] hover:text-white">
                    <MarketIcon />
                    <ChipLabel tone="inherit">Рынок Скинов</ChipLabel>
                </Chip>
            </Link>
            <Link href="/provably-fair">
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
        <header className="flex p-2.5 justify-between items-center w-full">
            <Logo />
            <div className="flex items-center gap-3">
                <div className="hidden 1155:flex items-center gap-3">
                    <StatsChips />
                    <Divider />
                </div>
                <NavChips />
            </div>
            <div className="flex items-center md:gap-3 gap-2">
                <div className="hidden 1155:flex gap-1 items-stretch">
                    <SocialLinks />
                    <SoundMenu />
                    <LanguageMenu />
                </div>
                <Divider className="hidden 1155:flex" />
                {user ? (
                    <>
                        <DepositBlock />
                        <ProfileChip />
                    </>
                ) : (
                    <button
                        type="button"
                        onClick={() => window.dispatchEvent(new Event('show-login-modal'))}
                        style={{
                            background:
                                "radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)",
                            boxShadow: "0 0 0 0 #0E1E39",
                        }}
                        className="py-[12px] px-[14px] flex rounded-[12px] justify-center items-center gap-[5px] transition-all duration-200 hover:brightness-125 hover:shadow-[0_0_20px_rgba(30,60,120,0.6)] active:scale-[0.98] cursor-pointer"
                    >
                        <SteamIcon />
                        <span className="text-sm text-white font-sf-display font-medium leading-[120%]">
                            Войти
                        </span>
                    </button>
                )}
            </div>
        </header>
    );
}
