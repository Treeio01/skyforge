import { useState } from 'react';
import { InventoryIcon, UpgradeTargetIcon, SearchIcon } from '@/Components/UI/Icons';
import { SkeletonSkinCard } from '@/Components/UI/Skeleton';
import BottomSheet from '@/Components/UI/BottomSheet';
import SkinCard, { SkinEntry } from './SkinCard';
import EmptySkinCard from './EmptySkinCard';
import { PriceSort } from './UpgradeTargetToolbar';

interface MobileUpgradePanelsProps {
    inventoryItems: SkinEntry[];
    inventorySkin: SkinEntry | null;
    selectedInventory: string | number | null;
    panelLocked: boolean;
    onSelectInventory: (id: string | number) => void;
    targetItems: SkinEntry[];
    targetSkin: SkinEntry | null;
    selectedTarget: string | number | null;
    targetsLoading: boolean;
    priceSort: PriceSort;
    minPrice: string;
    search: string;
    onSelectTarget: (id: string | number) => void;
    onScrollEnd: () => void;
    onPriceSortChange: (v: PriceSort) => void;
    onMinPriceChange: (v: string) => void;
    onSearchChange: (v: string) => void;
}

function nextSort(prev: PriceSort): PriceSort {
    if (prev === null) return 'asc';
    if (prev === 'asc') return 'desc';
    return null;
}

export default function MobileUpgradePanels({
    inventoryItems,
    inventorySkin,
    selectedInventory,
    panelLocked,
    onSelectInventory,
    targetItems,
    targetSkin,
    selectedTarget,
    targetsLoading,
    priceSort,
    minPrice,
    search,
    onSelectTarget,
    onScrollEnd,
    onPriceSortChange,
    onMinPriceChange,
    onSearchChange,
}: MobileUpgradePanelsProps) {
    const [sheet, setSheet] = useState<'inventory' | 'target' | null>(null);
    const [filtersOpen, setFiltersOpen] = useState(false);

    function handleSelectInventory(id: string | number) {
        onSelectInventory(id);
        setSheet(null);
    }

    function handleSelectTarget(id: string | number) {
        onSelectTarget(id);
        setSheet(null);
    }

    const filterButton = (
        <button
            type="button"
            onClick={(e) => { e.stopPropagation(); setFiltersOpen(true); }}
            className="flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white/6 hover:bg-white/12 transition-colors"
        >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2 4h10M4 7h6M6 10h2" stroke="white" strokeOpacity="0.5" strokeWidth="1.3" strokeLinecap="round" />
            </svg>
            <span className="text-white/50 font-sf-display text-[11px]">Фильтры</span>
        </button>
    );

    return (
        <>
            <div className="flex z-[100] w-full 1024:hidden">
                <div className="flex flex-col flex-1 overflow-hidden rounded-t-[14px] bg-accent/90">
                    <div className="flex items-center gap-[5px] bg-accent px-3.5 py-[6.5px]">
                        <InventoryIcon />
                        <span className="text-white font-sf-display text-[13px] leading-[104%]">Ваши скины</span>
                    </div>
                    <div className="p-2.5">
                        {inventorySkin
                            ? <SkinCard {...inventorySkin} selected onClick={panelLocked ? undefined : () => setSheet('inventory')} />
                            : <EmptySkinCard onClick={panelLocked ? undefined : () => setSheet('inventory')} />
                        }
                    </div>
                </div>

                <div className="flex flex-col flex-1 overflow-hidden rounded-t-[14px] bg-accent/90">
                    <div className="flex items-center gap-[5px] bg-accent px-3.5 py-[6.5px]">
                        <UpgradeTargetIcon />
                        <span className="text-white font-sf-display text-[13px] leading-[104%]">Скин апгрейда</span>
                    </div>
                    <div className="p-2.5">
                        {targetSkin
                            ? <SkinCard {...targetSkin} selected onClick={panelLocked ? undefined : () => setSheet('target')} />
                            : <EmptySkinCard onClick={panelLocked ? undefined : () => setSheet('target')} />
                        }
                    </div>
                </div>
            </div>

            <BottomSheet
                visible={sheet === 'inventory'}
                onClose={() => setSheet(null)}
                title="Ваши скины"
            >
                <div className="grid gap-[4px] grid-cols-[repeat(auto-fill,minmax(130px,1fr))]">
                    {inventoryItems.map((skin) => (
                        <SkinCard
                            key={skin.id}
                            {...skin}
                            selected={selectedInventory === skin.id}
                            dimmed={selectedInventory !== null && selectedInventory !== skin.id}
                            onClick={() => handleSelectInventory(skin.id)}
                        />
                    ))}
                    <EmptySkinCard />
                </div>
            </BottomSheet>

            <BottomSheet
                visible={sheet === 'target'}
                onClose={() => setSheet(null)}
                title="Скин апгрейда"
                headerRight={filterButton}
            >
                <div
                    className="grid gap-[4px] grid-cols-[repeat(auto-fill,minmax(130px,1fr))]"
                    onScroll={(e) => {
                        const el = e.currentTarget;
                        if (el.scrollHeight - el.scrollTop - el.clientHeight < 100) {
                            onScrollEnd();
                        }
                    }}
                >
                    {targetsLoading ? (
                        Array.from({ length: 12 }).map((_, i) => (
                            <SkeletonSkinCard key={`skel-${priceSort}-${minPrice}-${search}-${i}`} index={i} />
                        ))
                    ) : targetItems.length === 0 ? (
                        <div className="col-span-full flex flex-col items-center justify-center min-h-[260px] gap-1.5 text-center px-6">
                            <span className="text-white/55 font-gotham font-medium text-[14px] leading-[120%]">
                                Подходящих скинов не найдено
                            </span>
                            <span className="text-white/30 font-sf-display text-[11px] leading-[140%] max-w-[240px]">
                                Попробуйте сбросить фильтры или выбрать другой скин из инвентаря
                            </span>
                        </div>
                    ) : (
                        targetItems.map((skin) => (
                            <SkinCard
                                key={skin.id}
                                {...skin}
                                selected={selectedTarget === skin.id}
                                dimmed={selectedTarget !== null && selectedTarget !== skin.id}
                                onClick={() => handleSelectTarget(skin.id)}
                            />
                        ))
                    )}
                </div>
            </BottomSheet>

            {filtersOpen && (
                <div
                    className="fixed inset-0 z-[600] flex items-end 1024:hidden"
                    onClick={() => setFiltersOpen(false)}
                >
                    <div className="absolute inset-0 bg-black/50" />
                    <div
                        className="relative w-full rounded-t-[20px] p-5 flex flex-col gap-4"
                        style={{ background: '#0D1525', border: '1px solid rgba(255,255,255,0.08)', borderBottom: 'none' }}
                        onClick={(e) => e.stopPropagation()}
                    >
                        <div className="flex items-center justify-between">
                            <span className="text-white font-gotham font-medium text-[16px]">Фильтры</span>
                            <button
                                type="button"
                                onClick={() => setFiltersOpen(false)}
                                className="p-2 rounded-[8px] bg-white/6"
                            >
                                <svg width="12" height="12" viewBox="0 0 10 10" fill="none">
                                    <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.5" strokeLinecap="round" strokeWidth="1.2" />
                                </svg>
                            </button>
                        </div>

                        <div>
                            <label className="text-white/40 font-sf-display text-[11px] mb-1.5 block">Поиск</label>
                            <div className="flex items-center gap-2 px-3 py-2.5 rounded-[10px] bg-white/5 border border-white/8">
                                <input
                                    type="text"
                                    placeholder="Название скина"
                                    value={search}
                                    onChange={(e) => onSearchChange(e.target.value)}
                                    className="flex-1 outline-none text-white font-sf-display text-[13px] bg-transparent placeholder:text-white/25"
                                />
                                <SearchIcon />
                            </div>
                        </div>

                        <div>
                            <label className="text-white/40 font-sf-display text-[11px] mb-1.5 block">Минимальная цена</label>
                            <div className="flex items-center gap-2 px-3 py-2.5 rounded-[10px] bg-white/5 border border-white/8">
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    placeholder="0"
                                    value={minPrice}
                                    onChange={(e) => onMinPriceChange(e.target.value)}
                                    className="flex-1 outline-none text-white font-sf-display text-[13px] bg-transparent placeholder:text-white/25"
                                />
                                <span className="text-white/30 font-sf-display text-[12px]">₽</span>
                            </div>
                        </div>

                        <div>
                            <label className="text-white/40 font-sf-display text-[11px] mb-1.5 block">Сортировка по цене</label>
                            <div className="flex gap-2">
                                {([null, 'asc', 'desc'] as PriceSort[]).map((v) => (
                                    <button
                                        key={String(v)}
                                        type="button"
                                        onClick={() => onPriceSortChange(v)}
                                        className={`flex-1 py-2 rounded-[8px] font-sf-display text-[12px] transition-colors ${
                                            priceSort === v
                                                ? 'bg-brand text-white'
                                                : 'bg-white/6 text-white/40 hover:bg-white/10'
                                        }`}
                                    >
                                        {v === null ? 'По умолч.' : v === 'asc' ? 'Дешевле' : 'Дороже'}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={() => setFiltersOpen(false)}
                            className="w-full py-3 rounded-[12px] bg-brand text-white font-sf-display text-[14px] font-medium"
                        >
                            Применить
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
