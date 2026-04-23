import AppLayout from "@/Layouts/AppLayout";
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
            <div className="flex flex-col flex-1 min-h-0 w-full items-center">
                <div className="flex flex-col w-full max-w-[1664px] flex-1 min-h-0">
                    {/* Тулбар */}
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

                    {/* Грид скинов */}
                    <SkinGrid
                        items={market.items}
                        selected={market.selected}
                        onToggle={market.toggleSelect}
                        loading={market.loading}
                        containerRef={market.scrollRef}
                        onScroll={market.handleScroll}
                    />
                </div>
            </div>

            {/* Модалка корзины */}
            <Modal visible={market.cartOpen} onClose={() => market.setCartOpen(false)} maxWidth="max-w-[520px]">
                <div className="flex items-baseline gap-2">
                    <span className="text-white font-gotham font-medium text-xl leading-[100%]">Корзина</span>
                    <span className="text-white/40 font-sf-display text-[14px]">{market.selected.size} шт.</span>
                </div>

                {market.selectedItems.length > 0 ? (
                    <div className="flex gap-2 overflow-x-auto skins-scroll pb-2 -mx-[25px] px-[25px]">
                        {market.selectedItems.map((skin) => (
                            <div key={skin.id} className="shrink-0 relative">
                                <button
                                    onClick={() => market.toggleSelect(skin.id)}
                                    className="absolute right-1 top-1 z-20 p-1 rounded-[4px] bg-white/6 hover:bg-white/15 cursor-pointer transition-colors"
                                >
                                    <svg width="8" height="8" viewBox="0 0 10 10" fill="none">
                                        <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.4" strokeLinecap="round" />
                                    </svg>
                                </button>
                                <SkinCard {...skin} />
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="flex items-center justify-center py-8">
                        <span className="text-white/20 font-sf-display text-[13px]">Корзина пуста</span>
                    </div>
                )}

                <button
                    onClick={() => { market.clearSelected(); market.setCartOpen(false); }}
                    className="text-white/20 font-sf-display text-[13px] cursor-pointer hover:text-white/40 transition-colors text-center"
                >
                    Отменить все выделенное
                </button>

                <button
                    onClick={market.handleBuy}
                    disabled={market.selected.size === 0 || market.buying}
                    style={{ background: market.selected.size > 0 ? 'radial-gradient(80.57% 100% at 50% 100%, #4F86F5 0%, #05F 100%)' : undefined }}
                    className={`w-full py-4 rounded-[76px] flex justify-center items-center transition-all duration-200 ${
                        market.selected.size > 0
                            ? 'cursor-pointer hover:brightness-110 active:scale-[0.98]'
                            : 'bg-white/5 opacity-40 cursor-not-allowed'
                    }`}
                >
                    <span className="text-white font-sf-display text-[16px] font-medium leading-[120%]">
                        {market.buying ? 'Покупка...' : `${formatKopecks(market.totalSelected)}  |  Приобрести`}
                    </span>
                </button>
            </Modal>

            {/* Модалка фильтров */}
            <Modal visible={market.filtersOpen} onClose={() => market.setFiltersOpen(false)} maxWidth="max-w-[360px]">
                <span className="text-white font-gotham font-medium text-lg leading-[100%]">Фильтры</span>
                <div className="flex flex-col gap-3">
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
                </div>
                <button
                    onClick={() => { market.applyFilters(); market.setFiltersOpen(false); }}
                    style={{ background: "radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)" }}
                    className="w-full py-3 rounded-[12px] flex justify-center items-center cursor-pointer hover:brightness-125 active:scale-[0.98] transition-all"
                >
                    <span className="text-white font-sf-display text-[13px] font-medium">Применить</span>
                </button>
            </Modal>
        </AppLayout>
    );
}
