import { memo } from 'react';
import { formatKopecks } from "@/utils/skinHelpers";
import type { SortOption } from "./useMarket";
import { SORT_OPTIONS } from "./useMarket";
import Input from "@/Components/UI/Input";
import Button from "@/Components/UI/Button";

interface MarketToolbarProps {
    search: string;
    onSearchChange: (v: string) => void;
    selectedCount: number;
    totalSelected: number;
    onClearSelected: () => void;
    onOpenFilters: () => void;
    onOpenCart: () => void;
    buying?: boolean;
}

const MarketToolbar = memo(function MarketToolbar({
    search,
    onSearchChange,
    selectedCount,
    totalSelected,
    onClearSelected,
    onOpenFilters,
    onOpenCart,
    buying = false,
}: MarketToolbarProps) {
    return (
        <div className="w-full flex flex-col xs:flex-row xs:items-center gap-2 min-w-0">
            {/* Поиск */}
            <div className="w-full xs:w-[160px] 1024:w-[240px]">
                <Input
                    type="text"
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Поиск"
                />
            </div>

            {/* Кнопка фильтров */}
            <button
                onClick={onOpenFilters}
                className="flex items-center justify-center h-10 px-3.5 rounded-[10px] bg-[#161B26] hover:bg-[#1B2230] active:bg-[#232B3D] gap-1.5 cursor-pointer transition-colors duration-200 text-white/70 hover:text-white shrink-0"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M2.33301 3.5H11.6663M3.49967 7H10.4997M4.66634 10.5H9.33301" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.4" />
                </svg>
                <span className="font-sf-display text-[12px] leading-[100%] hidden xs:inline">Фильтры</span>
            </button>

            {/* Отменить выделенное */}
            <button
                onClick={onClearSelected}
                disabled={selectedCount === 0}
                title="Отменить выделение"
                className="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#161B26] hover:bg-[#1B2230] cursor-pointer transition-colors duration-200 text-white/55 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-[#161B26] disabled:hover:text-white/55 shrink-0"
            >
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M3.5 3.5L10.5 10.5M10.5 3.5L3.5 10.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                </svg>
            </button>

            <Button
                loading={buying}
                onClick={onOpenCart}
                className="relative w-full max-w-[120px] rounded-[12px] py-[7px] pl-2.5 pr-[52px] 1024:ml-auto"
                style={{ background: "linear-gradient(90deg, #FE7A02 0%, #FE4D00 100%)" }}
            >
                <div className="flex items-start flex-col">
                    <span className="text-[10px] leading-[120%] text-white">
                        {selectedCount > 0 ? `${selectedCount} шт.` : '0 шт.'}
                    </span>
                    <span className="font-sf-display text-sm leading-[120%] text-white font-medium whitespace-nowrap">
                        {formatKopecks(totalSelected)}
                    </span>
                </div>
                <img src="/assets/img/bucket.svg" className="absolute bottom-0 right-0" alt="" />
            </Button>
        </div>
    );
});

export default MarketToolbar;

interface FilterControlsProps {
    minPrice: string;
    onMinPriceChange: (v: string) => void;
    maxPrice: string;
    onMaxPriceChange: (v: string) => void;
    sortOption: SortOption;
    onSortChange: (v: SortOption) => void;
    onClose: () => void;
    onApply?: () => void;
}

export function FilterControls({
    minPrice,
    onMinPriceChange,
    maxPrice,
    onMaxPriceChange,
    sortOption,
    onSortChange,
    onClose,
    onApply,
}: FilterControlsProps) {
    return (
        <div className="flex flex-col gap-6">
            {/* Цена */}
            <div className="flex flex-col gap-2">
                <span className="text-white/40 font-sf-display text-[11px] uppercase tracking-[0.08em]">Цена</span>
                <div className="grid grid-cols-2 gap-2">
                    <Input
                        type="number"
                        inputMode="numeric"
                        value={minPrice}
                        onChange={(e) => onMinPriceChange(e.target.value)}
                        placeholder="0"
                        prefix="от"
                    />
                    <Input
                        type="number"
                        inputMode="numeric"
                        value={maxPrice}
                        onChange={(e) => onMaxPriceChange(e.target.value)}
                        placeholder="∞"
                        prefix="до"
                    />
                </div>
            </div>

            {/* Сортировка */}
            <div className="flex flex-col gap-2">
                <span className="text-white/40 font-sf-display text-[11px] uppercase tracking-[0.08em]">Сортировка</span>
                <div className="grid grid-cols-2 gap-2">
                    {SORT_OPTIONS.map((opt) => {
                        const isActive = sortOption === opt.value;
                        return (
                            <button
                                key={opt.value}
                                onClick={() => onSortChange(opt.value)}
                                className={`flex items-center justify-center h-10 px-3 rounded-[10px] cursor-pointer transition-colors duration-200 font-sf-display text-[12px] leading-[100%] ${
                                    isActive
                                        ? 'bg-[#232B3D] text-white'
                                        : 'bg-[#161B26] text-white/55 hover:bg-[#1B2230] hover:text-white'
                                }`}
                            >
                                {opt.label}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* Кнопки */}
            <div className="flex flex-col gap-2">
                <button
                    onClick={() => { onApply?.(); onClose(); }}
                    style={{ background: 'radial-gradient(80.57% 100% at 50% 100%, #4F86F5 0%, #05F 100%)' }}
                    className="flex items-center justify-center w-full h-12 rounded-[12px] cursor-pointer hover:brightness-110 active:scale-[0.98] transition-all duration-150"
                >
                    <span className="text-white font-sf-display text-[13px] font-medium">Применить</span>
                </button>
                <button
                    onClick={onClose}
                    className="flex items-center justify-center w-full h-10 rounded-[10px] bg-[#161B26] hover:bg-[#1B2230] cursor-pointer transition-colors duration-200"
                >
                    <span className="text-white/65 font-sf-display text-[12px]">Закрыть</span>
                </button>
            </div>
        </div>
    );
}
