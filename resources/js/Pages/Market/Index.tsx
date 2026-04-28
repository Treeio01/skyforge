import AppLayout from "@/Layouts/AppLayout";
import PageShell from "@/Components/Layout/PageShell";
import { MarketIcon } from "@/Components/UI/Icons";
import Modal from "@/Components/UI/Modal";
import SkinCard from "@/Components/Upgrade/SkinCard";
import MarketToolbar, { FilterControls } from "@/Components/Market/MarketToolbar";
import SkinGrid from "@/Components/Market/SkinGrid";
import { useMarket } from "@/Components/Market/useMarket";
import { useAuthGuard } from "@/hooks/useAuthGuard";
import { formatKopecks } from "@/utils/skinHelpers";

export default function MarketIndex() {
    const { guard } = useAuthGuard();
    const market = useMarket();

    return (
        <AppLayout>
            <PageShell
                icon={<MarketIcon />}
                title="Рынок скинов"
                subtitle="Сортировка и фильтры"
                toolbar={
                    <MarketToolbar
                        search={market.search}
                        onSearchChange={market.setSearch}
                        selectedCount={market.selected.size}
                        totalSelected={market.totalSelected}
                        onClearSelected={market.clearSelected}
                        onOpenFilters={() => market.setFiltersOpen(true)}
                        onOpenCart={() => guard(() => market.setCartOpen(true))}
                        buying={market.buying}
                    />
                }
            >
                <div className="flex flex-col flex-1 min-h-0 max-h-[calc(100svh-220px)] p-3 1024:p-4 rounded-[14px] bg-[#11161F]">
                    <SkinGrid
                        items={market.items}
                        selected={market.selected}
                        onToggle={market.toggleSelect}
                        loading={market.loading}
                        hasMore={market.hasMore}
                        containerRef={market.scrollRef}
                        onScroll={market.handleScroll}
                    />
                </div>
            </PageShell>

            {/* Модалка корзины */}
            <Modal visible={market.cartOpen} onClose={() => market.setCartOpen(false)} maxWidth="max-w-[560px]">
                <div className="flex items-center justify-between">
                    <div className="flex items-baseline gap-2">
                        <span className="text-white font-gotham font-medium text-xl leading-[100%]">Корзина</span>
                        <span className="text-white/40 font-sf-display text-[13px]">{market.selected.size} шт.</span>
                    </div>
                    <button
                        onClick={() => market.setCartOpen(false)}
                        className="flex items-center justify-center w-8 h-8 rounded-[8px] bg-white/5 hover:bg-white/10 cursor-pointer transition-colors"
                    >
                        <svg width="12" height="12" viewBox="0 0 10 10" fill="none">
                            <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.6" strokeLinecap="round" strokeWidth="1.4" />
                        </svg>
                    </button>
                </div>

                {market.selectedItems.length > 0 ? (
                    <div className="flex gap-2 overflow-x-auto skins-scroll pb-2 -mx-[25px] px-[25px]">
                        {market.selectedItems.map((skin) => (
                            <div key={skin.id} className="shrink-0 relative w-[145px]">
                                <button
                                    onClick={() => market.toggleSelect(skin)}
                                    className="absolute right-1 top-1 z-20 flex items-center justify-center w-6 h-6 rounded-[6px] bg-black/50 hover:bg-black/80 backdrop-blur cursor-pointer transition-colors"
                                >
                                    <svg width="8" height="8" viewBox="0 0 10 10" fill="none">
                                        <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.7" strokeLinecap="round" strokeWidth="1.5" />
                                    </svg>
                                </button>
                                <SkinCard {...skin} />
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center py-10 gap-1.5 rounded-[12px] bg-[#11161F]">
                        <span className="text-white/40 font-sf-display text-[13px]">Корзина пуста</span>
                        <span className="text-white/20 font-sf-display text-[11px]">Выберите скины в гриде</span>
                    </div>
                )}

                <div className="flex flex-col gap-2 pt-1">
                    <button
                        onClick={market.handleBuy}
                        disabled={market.selected.size === 0 || market.buying}
                        style={{ background: market.selected.size > 0 ? 'radial-gradient(80.57% 100% at 50% 100%, #4F86F5 0%, #05F 100%)' : undefined }}
                        className={`w-full py-3.5 rounded-[12px] flex justify-center items-center transition-all duration-200 ${
                            market.selected.size > 0
                                ? 'cursor-pointer hover:brightness-110 active:scale-[0.98]'
                                : 'bg-white/5 opacity-40 cursor-not-allowed'
                        }`}
                    >
                        <span className="text-white font-sf-display text-[15px] font-medium leading-[120%]">
                            {market.buying ? 'Покупка...' : `${formatKopecks(market.totalSelected)} · Приобрести`}
                        </span>
                    </button>
                    {market.selected.size > 0 && (
                        <button
                            onClick={() => { market.clearSelected(); market.setCartOpen(false); }}
                            className="w-full py-2.5 rounded-[12px] bg-white/5 hover:bg-white/10 cursor-pointer transition-colors duration-150"
                        >
                            <span className="text-white/60 font-sf-display text-[12px]">Очистить корзину</span>
                        </button>
                    )}
                </div>
            </Modal>

            {/* Модалка фильтров */}
            <Modal visible={market.filtersOpen} onClose={() => market.setFiltersOpen(false)} maxWidth="max-w-[400px]">
                <div className="flex items-center justify-between">
                    <span className="text-white font-gotham font-medium text-xl leading-[100%]">Фильтры</span>
                    <button
                        onClick={() => market.setFiltersOpen(false)}
                        className="flex items-center justify-center w-8 h-8 rounded-[8px] bg-white/5 hover:bg-white/10 cursor-pointer transition-colors"
                    >
                        <svg width="12" height="12" viewBox="0 0 10 10" fill="none">
                            <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.6" strokeLinecap="round" strokeWidth="1.4" />
                        </svg>
                    </button>
                </div>
                <FilterControls
                    minPrice={market.minPrice}
                    onMinPriceChange={market.setMinPrice}
                    maxPrice={market.maxPrice}
                    onMaxPriceChange={market.setMaxPrice}
                    sortOption={market.sortOption}
                    onSortChange={market.setSortOption}
                    onClose={() => market.setFiltersOpen(false)}
                    onApply={market.applyFilters}
                />
            </Modal>
        </AppLayout>
    );
}
