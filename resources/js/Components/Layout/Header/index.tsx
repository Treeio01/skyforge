import { Link, usePage } from "@inertiajs/react";
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon } from "./icons";
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
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="14"
                            height="14"
                            viewBox="0 0 14 14"
                            fill="none"
                        >
                            <path
                                d="M6.6809 5.49624L5.18115 7.6735C4.82761 7.65744 4.4717 7.75746 4.17626 7.95162L0.883014 6.59677C0.883014 6.59677 0.80681 7.84964 1.12438 8.78335L3.45253 9.7434C3.56943 10.2655 3.92779 10.7234 4.45652 10.9437C5.32154 11.3049 6.31896 10.8932 6.6788 10.0283C6.77247 9.80224 6.81613 9.56516 6.80979 9.32856L9.00831 7.79705C10.2925 7.79705 11.3362 6.75081 11.3362 5.46596C11.3362 4.18104 10.2925 3.13574 9.00831 3.13574C7.76796 3.13574 6.61138 4.21802 6.6809 5.49624ZM6.32053 9.87798C6.04202 10.5462 5.27364 10.8632 4.60571 10.5851C4.29758 10.4568 4.06495 10.2218 3.93074 9.94159L4.68857 10.2555C5.18115 10.4605 5.74627 10.2271 5.95102 9.73496C6.15642 9.24233 5.92341 8.67664 5.43109 8.47159L4.64771 8.14718C4.94997 8.0326 5.29363 8.0284 5.61471 8.16193C5.93837 8.2965 6.18954 8.54994 6.32263 8.87378C6.45576 9.19766 6.45524 9.55514 6.32053 9.87798ZM9.00831 7.01896C8.15344 7.01896 7.45742 6.32233 7.45742 5.46596C7.45742 4.6103 8.15344 3.91349 9.00831 3.91349C9.86376 3.91349 10.5597 4.6103 10.5597 5.46596C10.5597 6.32233 9.86376 7.01896 9.00831 7.01896ZM7.84618 5.4636C7.84618 4.81943 8.36807 4.29692 9.01094 4.29692C9.65437 4.29692 10.1762 4.81943 10.1762 5.4636C10.1762 6.10786 9.65437 6.62989 9.01094 6.62989C8.36807 6.62989 7.84618 6.10786 7.84618 5.4636Z"
                                fill="white"
                            />
                        </svg>
                        <span className="text-sm text-white font-sf-display font-medium leading-[120%]">
                            Войти
                        </span>
                    </button>
                )}
            </div>
        </header>
    );
}
