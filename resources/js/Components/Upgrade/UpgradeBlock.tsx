import { motion } from "framer-motion";
import { Skin } from "@/types";
import { PageTitleIcon } from "@/Components/UI/Icons";
import UpgradeResult from "./UpgradeResult";
import UpgradeVideo from "./UpgradeVideo";
import UpgradeInventoryPanel from "./UpgradeInventoryPanel";
import UpgradeTargetPanel from "./UpgradeTargetPanel";
import UpgradeMultiplierBar from "./UpgradeMultiplierBar";
import UpgradeLoginModal from "./UpgradeLoginModal";
import MobileUpgradePanels from "./MobileUpgradePanels";
import { useUpgrade } from "./useUpgrade";

interface UpgradeBlockProps {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
}

export type { Stage, QuickMultiplier } from "./useUpgrade";
export { MULTIPLIERS } from "./useUpgrade";

export default function UpgradeBlock({ inventory }: UpgradeBlockProps) {
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
            <UpgradeLoginModal
                visible={modalVisible && isGuest}
                adultChecked={adultChecked}
                termsChecked={termsChecked}
                canLogin={canLogin}
                setAdultChecked={setAdultChecked}
                setTermsChecked={setTermsChecked}
            />
            <div className="pt-[6px] px-[6px] pb-[6px] flex flex-col">
                <div className="flex flex-col justify-between w-full rounded-t-[24px] bg-[#080B10] relative overflow-hidden min-h-[calc(100svh-72px)] 1024:min-h-0 1024:h-[calc(100dvh-12px)]">
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
                        <motion.div
                            className="absolute inset-0 z-[100] pointer-events-none"
                            initial="idle"
                            variants={{
                                idle: { x: 0 },
                                shake: {
                                    x: [-8, 8, -8, 8, 0],
                                    transition: { duration: 0.4 },
                                },
                            }}
                            animate={outcome === "fail" ? "shake" : "idle"}
                        >
                            <UpgradeResult
                                variant={outcome === "success" ? "win" : "lose"}
                                skin={resultSkin}
                            />
                        </motion.div>
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
                        <UpgradeMultiplierBar
                            activeQuick={activeQuick}
                            onMultiplierChange={handleMultiplierChange}
                        />
                    )}

                    {/* Мобильные кнопки-селекторы (< 1024px) */}
                    <MobileUpgradePanels
                        inventoryItems={inventoryItems}
                        inventorySkin={inventorySkin}
                        selectedInventory={selectedInventory}
                        panelLocked={panelLocked}
                        onSelectInventory={handleSelectInventory}
                        targetItems={targetItems}
                        targetSkin={targetSkin}
                        selectedTarget={selectedTarget}
                        targetsLoading={targetsLoading}
                        priceSort={priceSort}
                        minPrice={minPrice}
                        search={search}
                        onSelectTarget={handleSelectTarget}
                        onScrollEnd={loadMore}
                        onPriceSortChange={setPriceSort}
                        onMinPriceChange={setMinPrice}
                        onSearchChange={setSearch}
                    />

                    {/* Десктопные панели (>= 1024px) */}
                    <div className="hidden z-50 1024:flex w-full 1024:flex-row 1024:items-end 1024:justify-between 1024:gap-[20px] 1024:w-full 1024:px-0 1024:pb-0">
                        <UpgradeInventoryPanel
                            inventoryItems={inventoryItems}
                            selectedInventory={selectedInventory}
                            panelLocked={panelLocked}
                            onSelectInventory={handleSelectInventory}
                        />

                        <UpgradeTargetPanel
                            targetItems={targetItems}
                            selectedTarget={selectedTarget}
                            panelLocked={panelLocked}
                            targetsLoading={targetsLoading}
                            priceSort={priceSort}
                            minPrice={minPrice}
                            search={search}
                            onSelectTarget={handleSelectTarget}
                            onScrollEnd={loadMore}
                            onPriceSortChange={setPriceSort}
                            onMinPriceChange={setMinPrice}
                            onSearchChange={setSearch}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}
