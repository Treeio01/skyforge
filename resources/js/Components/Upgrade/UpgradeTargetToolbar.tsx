import { ChevronDownIcon, SearchIcon } from '@/Components/UI/Icons';

export type PriceSort = 'asc' | 'desc' | null;

interface UpgradeTargetToolbarProps {
    priceSort: PriceSort;
    onPriceSortChange: (next: PriceSort) => void;
    minPrice: string;
    onMinPriceChange: (value: string) => void;
    search: string;
    onSearchChange: (value: string) => void;
}

function nextSort(prev: PriceSort): PriceSort {
    if (prev === null) return 'asc';
    if (prev === 'asc') return 'desc';
    return null;
}

export default function UpgradeTargetToolbar({
    priceSort,
    onPriceSortChange,
    minPrice,
    onMinPriceChange,
    search,
    onSearchChange,
}: UpgradeTargetToolbarProps) {
    return (
        <div className="flex gap-2 items-center">
            <button
                type="button"
                onClick={() => onPriceSortChange(nextSort(priceSort))}
                className={`flex py-[9.5px] px-[11px] rounded-[8px] gap-[2px] items-center cursor-pointer transition-colors duration-150 ${
                    priceSort ? 'bg-[#1A2230]' : 'bg-[#0A0E17]'
                }`}
            >
                <span className="text-white text-[11px] font-sf-display leading-[104%]">
                    Цена
                </span>
                <span
                    className="flex transition-transform duration-200"
                    style={{
                        transform:
                            priceSort === 'asc' ? 'rotate(180deg)' : 'rotate(0)',
                    }}
                >
                    <ChevronDownIcon />
                </span>
            </button>
            <span className="text-[#22272F] text-[11px] font-sf-display leading-[104%]">
                от
            </span>
            <div className="flex py-2.5 px-[11px] rounded-[8px] bg-[#06080D]">
                <input
                    type="number"
                    inputMode="numeric"
                    placeholder="0"
                    value={minPrice}
                    onChange={(e) => onMinPriceChange(e.target.value)}
                    className="w-full outline-none max-w-[35px] text-white font-sf-display text-[11px] leading-[104%] placeholder:text-[#313743]"
                />
            </div>
            <div className="flex py-2.5 px-[11px] items-center gap-[2px] w-full max-w-[141px] rounded-[8px] bg-[#06080D]">
                <input
                    type="text"
                    placeholder="Поиск"
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    className="w-full outline-none max-w-[109px] text-white font-sf-display text-[11px] leading-[104%] placeholder:text-[#313743]"
                />
                <SearchIcon />
            </div>
        </div>
    );
}
