import { useTranslation } from 'react-i18next';
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
    const { t } = useTranslation();
    return (
        <div className="flex gap-2 items-center">
            <button
                type="button"
                onClick={() => onPriceSortChange(nextSort(priceSort))}
                className={`flex py-[9.5px] px-[11px] rounded-[8px] gap-1 items-center cursor-pointer transition-colors duration-200 ${
                    priceSort
                        ? 'bg-white/12 text-white'
                        : 'bg-white/5 text-white/70 hover:bg-white/8 hover:text-white active:bg-white/15'
                }`}
            >
                <span className="text-[11px] font-sf-display leading-[104%]">
                    {t('common.price')}
                </span>
                <span
                    className="flex transition-transform duration-300"
                    style={{
                        transform: priceSort === 'asc' ? 'rotate(180deg)' : 'rotate(0)',
                        opacity: priceSort ? 1 : 0.6,
                    }}
                >
                    <ChevronDownIcon />
                </span>
            </button>
            <span className="text-white/30 text-[11px] font-sf-display leading-[104%]">
                {t('common.from')}
            </span>
            <div className={`flex py-2.5 px-[11px] rounded-[8px] bg-white/4 transition-all duration-200 ring-1 ring-inset ${minPrice ? 'ring-white/15' : 'ring-transparent'} hover:bg-white/6 focus-within:bg-white/8 focus-within:ring-white/25`}>
                <input
                    type="number"
                    inputMode="numeric"
                    placeholder="0"
                    value={minPrice}
                    onChange={(e) => onMinPriceChange(e.target.value)}
                    className="w-full outline-none max-w-[35px] text-white font-sf-display text-[11px] leading-[104%] placeholder:text-white/30"
                />
            </div>
            <div className={`flex py-2.5 px-[11px] items-center gap-[2px] w-full max-w-[141px] rounded-[8px] bg-white/4 transition-all duration-200 ring-1 ring-inset ${search ? 'ring-white/15' : 'ring-transparent'} hover:bg-white/6 focus-within:bg-white/8 focus-within:ring-white/25`}>
                <input
                    type="text"
                    placeholder={t('common.search')}
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    className="w-full outline-none max-w-[109px] text-white font-sf-display text-[11px] leading-[104%] placeholder:text-white/30"
                />
                <span className={`flex transition-opacity duration-200 ${search ? 'opacity-90' : 'opacity-50'}`}>
                    <SearchIcon />
                </span>
            </div>
        </div>
    );
}
