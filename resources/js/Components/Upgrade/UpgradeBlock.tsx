import { Skin } from "@/types";
import { InventoryIcon, PageTitleIcon, UpgradeTargetIcon } from "@/Components/UI/Icons";
import SkinCard from "./SkinCard";
import SkinsPanel from "./SkinsPanel";
import UpgradeResult from "./UpgradeResult";
import UpgradeTargetToolbar from "./UpgradeTargetToolbar";
import UpgradeVideo from "./UpgradeVideo";
import EmptySkinCard from "./EmptySkinCard";
import { useUpgrade, MULTIPLIERS } from "./useUpgrade";

interface UpgradeBlockProps {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
    balance: number;
}

export type { Stage, QuickMultiplier } from "./useUpgrade";
export { MULTIPLIERS } from "./useUpgrade";

export default function UpgradeBlock({ inventory, balance: _balance }: UpgradeBlockProps) {
    const upgrade = useUpgrade({ inventory });

    const {
        selectedInventory,
        selectedTarget,
        handleSelectInventory,
        handleSelectTarget,
        priceSort,
        setPriceSort,
        minPrice,
        setMinPrice,
        search,
        setSearch,
        multiplier,
        activeQuick,
        stage,
        outcome,
        resultSkin,
        modalVisible,
        adultChecked,
        setAdultChecked,
        termsChecked,
        setTermsChecked,
        canLogin,
        isGuest,
        inventoryItems,
        inventorySkin,
        targetItems,
        targetSkin,
        chance,
        canStart,
        videoState,
        panelLocked,
        targetsLoading,
        loadMore,
        handleMultiplierChange,
        handleGo,
        handleClosingComplete,
        handleVideoEnded,
        handleRemoveInventory,
        handleRemoveTarget,
    } = upgrade;

    const videoProps = {
        state: videoState,
        inventorySkin,
        targetSkin,
        multiplier,
        activeQuick,
        onVideoEnded: handleVideoEnded,
        onClosingComplete: handleClosingComplete,
        onMultiplierChange: handleMultiplierChange,
        chance,
        stage,
        outcome,
        canStart,
        onGo: handleGo,
        onRemoveInventory: handleRemoveInventory,
        onRemoveTarget: handleRemoveTarget,
    };

    return (
        <>
            <div
                className={`flex z-[1000] justify-center items-center bg-black/40 absolute w-full h-full top-0 left-0 transition-opacity duration-500 ease-out ${
                    modalVisible && isGuest
                        ? "opacity-100"
                        : "opacity-0 pointer-events-none"
                }`}
            >
                <div
                    style={{
                        border: "1px solid rgba(255, 255, 255, 0.21)",

                        background:
                            "radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)",

                        boxShadow: "0 26px 80px 0 rgba(0, 0, 0, 0.30)",
                    }}
                    className={`flex rounded-[20px] backdrop-blur-[70px] w-full max-w-[490px] p-[25px] gap-[25px] flex-col transition-all duration-500 ease-out ${
                        modalVisible
                            ? "opacity-100 scale-100 translate-y-0"
                            : "opacity-0 scale-95 translate-y-4"
                    }`}
                >
                    <div className="flex w-full">
                        <div className="flex w-full flex-col gap-[3px]">
                            <span className="text-white font-gotham font-medium text-2xl leading-[100%]">
                                Добро пожаловать!
                            </span>
                            <p className="font-inter text-sm leading-[100%]">
                                Текст ляллялляляля и вот так лялялля
                            </p>
                        </div>

                        <svg
                            width="10"
                            height="10"
                            viewBox="0 0 10 10"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M7.38092 7.38105L2.61902 2.61914M7.38092 2.61914L2.61902 7.38105"
                                stroke="white"
                                strokeOpacity="0.32"
                                strokeWidth="0.476191"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </div>
                    <div className="flex flex-col gap-2.5">
                        <label
                            htmlFor="adult"
                            className="text-white font-inter text-[12px] flex gap-[5px] items-center cursor-pointer"
                        >
                            <input
                                id="adult"
                                type="checkbox"
                                checked={adultChecked}
                                onChange={(e) => setAdultChecked(e.target.checked)}
                            />
                            Я подтверждаю, что мне больше 18 лет
                        </label>

                        <label
                            htmlFor="terms"
                            className="text-white font-inter text-[12px] flex gap-[5px] items-center cursor-pointer"
                        >
                            <input
                                id="terms"
                                type="checkbox"
                                checked={termsChecked}
                                onChange={(e) => setTermsChecked(e.target.checked)}
                            />
                            Я принимаю правила и условия использования сайта
                        </label>
                    </div>

                    <a
                        href={canLogin ? "/auth/steam" : undefined}
                        onClick={(e) => { if (!canLogin) e.preventDefault(); }}
                        style={{
                            background: "radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)",
                            boxShadow: "0 0 0 0 #0E1E39",
                        }}
                        className={`py-[18px] px-[14px] flex rounded-[12px] justify-center items-center gap-[5px] transition-all duration-200 ${
                            canLogin
                                ? "hover:brightness-125 hover:shadow-[0_0_20px_rgba(30,60,120,0.6)] active:scale-[0.98] cursor-pointer"
                                : "opacity-40 cursor-not-allowed"
                        }`}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M6.6809 5.49624L5.18115 7.6735C4.82761 7.65744 4.4717 7.75746 4.17626 7.95162L0.883014 6.59677C0.883014 6.59677 0.80681 7.84964 1.12438 8.78335L3.45253 9.7434C3.56943 10.2655 3.92779 10.7234 4.45652 10.9437C5.32154 11.3049 6.31896 10.8932 6.6788 10.0283C6.77247 9.80224 6.81613 9.56516 6.80979 9.32856L9.00831 7.79705C10.2925 7.79705 11.3362 6.75081 11.3362 5.46596C11.3362 4.18104 10.2925 3.13574 9.00831 3.13574C7.76796 3.13574 6.61138 4.21802 6.6809 5.49624ZM6.32053 9.87798C6.04202 10.5462 5.27364 10.8632 4.60571 10.5851C4.29758 10.4568 4.06495 10.2218 3.93074 9.94159L4.68857 10.2555C5.18115 10.4605 5.74627 10.2271 5.95102 9.73496C6.15642 9.24233 5.92341 8.67664 5.43109 8.47159L4.64771 8.14718C4.94997 8.0326 5.29363 8.0284 5.61471 8.16193C5.93837 8.2965 6.18954 8.54994 6.32263 8.87378C6.45576 9.19766 6.45524 9.55514 6.32053 9.87798ZM9.00831 7.01896C8.15344 7.01896 7.45742 6.32233 7.45742 5.46596C7.45742 4.6103 8.15344 3.91349 9.00831 3.91349C9.86376 3.91349 10.5597 4.6103 10.5597 5.46596C10.5597 6.32233 9.86376 7.01896 9.00831 7.01896ZM7.84618 5.4636C7.84618 4.81943 8.36807 4.29692 9.01094 4.29692C9.65437 4.29692 10.1762 4.81943 10.1762 5.4636C10.1762 6.10786 9.65437 6.62989 9.01094 6.62989C8.36807 6.62989 7.84618 6.10786 7.84618 5.4636Z" fill="white" />
                        </svg>
                        <span className="text-sm text-white font-sf-display font-medium leading-[120%]">
                            Войти через Steam
                        </span>
                    </a>
                </div>
            </div>
            <div className="pt-[6px] px-[6px] flex-1 min-h-0 flex flex-col">
                <div className="flex flex-col justify-between flex-1 min-h-0 w-full  rounded-t-[24px] bg-[#080B10] relative overflow-hidden">
                    <UpgradeVideo
                        {...videoProps}
                        device="mb"
                        className="block 402:hidden min-w-[820px] 450:min-w-[1000px] 550:min-w-[1100px] 1024:min-w-[1300px]"
                    />
                    <UpgradeVideo
                        {...videoProps}
                        device="md"
                        className="hidden !top-[-40px] 402:block 1024:hidden min-w-[820px] 450:min-w-[1000px] 550:min-w-[1100px] 1024:min-w-[1300px]"
                    />
                    <UpgradeVideo
                        {...videoProps}
                        device="pc"
                        className="hidden 1024:block min-w-[820px] 450:min-w-[1000px] 550:min-w-[1100px] 1024:min-w-[1300px]"
                    />

                    {stage === "result" && outcome && resultSkin && (
                        <UpgradeResult
                            variant={outcome === "success" ? "win" : "lose"}
                            skin={resultSkin}
                        />
                    )}

                    <div className="flex flex-col gap-1 z-50 md:pt-[32px] md:px-[44px] px-[16px] pt-[14px]">
                        <div className="flex items-center gap-1">
                            <PageTitleIcon />
                            <span className="text-white text-2xl md:text-[27px] leading-[104%] font-gotham font-medium">
                                Апгрейд
                            </span>
                        </div>
                        <span className="text-[#EFEFEF]/54 md:text-[#9C9DA9] md:text-base text-[12px] font-sf-display">
                            Улучшайте ваши предметы в пару кликов
                        </span>
                    </div>

                    {/* Мобильный блок иксов */}
                    {stage === "idle" && (
                        <div className="absolute z-[100] right-3 top-[35%] -translate-y-1/2 flex flex-col items-center gap-1 1024:hidden">
                                {MULTIPLIERS.map((m) => {
                                    const active = activeQuick === m;
                                    return (
                                        <button
                                            key={m}
                                            type="button"
                                            onClick={() => handleMultiplierChange(m)}
                                            className={`p-4 flex items-center justify-center rounded-[8px] cursor-pointer ${active ? 'bg-white/20' : 'bg-white/6'}`}
                                        >
                                            <span className={`font-gotham font-medium text-[12px] leading-[104%] ${active ? 'text-white' : 'text-white/29'}`}>x{m}</span>
                                        </button>
                                    );
                                })}
                        </div>
                    )}

                    <div className="absolute bottom-2 left-0 w-full 1024:px-2 1024:gap-2 z-[100] flex items-stretch 1024:static 1024:bottom-auto 1024:px-0 1024:gap-[20px] 1024:max-w-[1281px] 1024:self-center 1024:w-full max-h-[230px] 402:max-h-[280px] 1024:max-h-full">
                        <SkinsPanel icon={<InventoryIcon />} title="Ваши скины">
                            {inventoryItems.map((skin) => (
                                <SkinCard
                                    key={skin.id}
                                    {...skin}
                                    selected={selectedInventory === skin.id}
                                    dimmed={
                                        selectedInventory !== null &&
                                        selectedInventory !== skin.id
                                    }
                                    onClick={
                                        panelLocked
                                            ? undefined
                                            : () =>
                                                  handleSelectInventory(skin.id)
                                    }
                                />
                            ))}
                            <EmptySkinCard />
                        </SkinsPanel>

                        <SkinsPanel
                            icon={<UpgradeTargetIcon />}
                            title="Скин апгрейда"
                            onScrollEnd={loadMore}
                            toolbar={
                                <UpgradeTargetToolbar
                                    priceSort={priceSort}
                                    onPriceSortChange={setPriceSort}
                                    minPrice={minPrice}
                                    onMinPriceChange={setMinPrice}
                                    search={search}
                                    onSearchChange={setSearch}
                                />
                            }
                        >
                            {targetItems.map((skin) => (
                                <SkinCard
                                    key={skin.id}
                                    {...skin}
                                    selected={selectedTarget === skin.id}
                                    dimmed={
                                        selectedTarget !== null &&
                                        selectedTarget !== skin.id
                                    }
                                    onClick={
                                        panelLocked
                                            ? undefined
                                            : () => handleSelectTarget(skin.id)
                                    }
                                />
                            ))}
                            {targetsLoading && (
                                <div className="col-span-full text-center text-white/30 text-sm py-2">
                                    Загрузка...
                                </div>
                            )}
                        </SkinsPanel>
                    </div>
                </div>
            </div>
        </>
    );
}
