import { motion } from "framer-motion";
import { Skin } from "@/types";
import { PageTitleIcon } from "@/Components/UI/Icons";
import UpgradeResult from "./UpgradeResult";
import UpgradeVideo from "./UpgradeVideo";
import UpgradeInventoryPanel from "./UpgradeInventoryPanel";
import UpgradeTargetPanel from "./UpgradeTargetPanel";
import UpgradeMultiplierBar from "./UpgradeMultiplierBar";
import UpgradeLoginModal from "./UpgradeLoginModal";
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
                        <motion.div
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

                    <div className="absolute bottom-2 left-0 w-full 1024:px-2 1024:gap-2 z-[100] flex items-stretch 1024:static 1024:bottom-auto 1024:px-0 1024:gap-[20px] 1024:max-w-[1281px] 1024:self-center 1024:w-full max-h-[230px] 402:max-h-[280px] 1024:max-h-full">
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
