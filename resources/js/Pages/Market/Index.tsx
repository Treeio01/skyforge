import AppLayout from "@/Layouts/AppLayout";
import Modal from "@/Components/UI/Modal";
import SkinCard from "@/Components/Upgrade/SkinCard";
import { useTargetSkins } from "@/hooks/useTargetSkins";
import { apiSkinToEntry, formatKopecks } from "@/utils/skinHelpers";
import { useMemo, useRef, useState, useCallback } from "react";
import { router } from "@inertiajs/react";

type SortOption = "price_asc" | "price_desc" | "name_asc" | "name_desc";

const SORT_OPTIONS: { label: string; value: SortOption }[] = [
    { label: "Цена ↑", value: "price_asc" },
    { label: "Цена ↓", value: "price_desc" },
    { label: "Имя А-Я", value: "name_asc" },
    { label: "Имя Я-А", value: "name_desc" },
];

function parseSortOption(s: SortOption) {
    const [sort, dir] = s.split("_") as ["price" | "name", "asc" | "desc"];
    return { sort, direction: dir };
}

export default function MarketIndex() {
    const [search, setSearch] = useState("");
    const [sortOption, setSortOption] = useState<SortOption>("price_asc");
    const [minPrice, setMinPrice] = useState("");
    const [maxPrice, setMaxPrice] = useState("");
    const [filtersOpen, setFiltersOpen] = useState(false);
    const [cartOpen, setCartOpen] = useState(false);
    const [buying, setBuying] = useState(false);
    const [selected, setSelected] = useState<Set<string | number>>(new Set());

    const { sort, direction } = parseSortOption(sortOption);

    const { skins, loading, loadMore } = useTargetSkins({
        search,
        sort,
        direction,
        minPrice,
        maxPrice,
        perPage: 150,
        inventoryPrice: null,
    });

    const items = useMemo(() => skins.map((s) => apiSkinToEntry(s)), [skins]);

    const selectedItems = useMemo(
        () => items.filter((s) => selected.has(s.id)),
        [items, selected],
    );

    const totalSelected = selectedItems.reduce((sum, s) => sum + s.priceKopecks, 0);

    const toggleSelect = useCallback((id: string | number) => {
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    }, []);

    const scrollRef = useRef<HTMLDivElement>(null);

    const handleScroll = useCallback(() => {
        const el = scrollRef.current;
        if (!el) return;
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
            loadMore();
        }
    }, [loadMore]);

    const filterControls = (
        <>
            {/* Мин цена */}
            <div className="flex py-2 px-3 rounded-[8px] bg-[#0A0E17] items-center gap-1 w-full 1024:w-[90px]">
                <span className="text-[#313743] text-[11px] font-sf-display">от</span>
                <input
                    type="number"
                    inputMode="numeric"
                    value={minPrice}
                    onChange={(e) => setMinPrice(e.target.value)}
                    placeholder="0"
                    className="bg-transparent outline-none text-white font-sf-display text-[12px] leading-[104%] w-full placeholder:text-[#313743]"
                />
            </div>

            {/* Макс цена */}
            <div className="flex py-2 px-3 rounded-[8px] bg-[#0A0E17] items-center gap-1 w-full 1024:w-[90px]">
                <span className="text-[#313743] text-[11px] font-sf-display">до</span>
                <input
                    type="number"
                    inputMode="numeric"
                    value={maxPrice}
                    onChange={(e) => setMaxPrice(e.target.value)}
                    placeholder="∞"
                    className="bg-transparent outline-none text-white font-sf-display text-[12px] leading-[104%] w-full placeholder:text-[#313743]"
                />
            </div>

            {/* Сортировка */}
            <div className="flex gap-1 flex-wrap">
                {SORT_OPTIONS.map((opt) => (
                    <button
                        key={opt.value}
                        onClick={() => { setSortOption(opt.value); setFiltersOpen(false); }}
                        className={`py-2 px-2.5 rounded-[8px] cursor-pointer transition-colors font-sf-display text-[11px] leading-[104%] ${
                            sortOption === opt.value
                                ? "bg-[#1A2230] text-white"
                                : "bg-[#0A0E17] text-[#313743] hover:text-white/50"
                        }`}
                    >
                        {opt.label}
                    </button>
                ))}
            </div>
        </>
    );

    return (
        <AppLayout>
            <div className="flex flex-col flex-1 min-h-0 w-full items-center">
                <div className="flex flex-col w-full max-w-[1664px] flex-1 min-h-0">
                    {/* Тулбар */}
                    <div className="flex flex-wrap gap-2 p-3 items-center justify-between bg-[#070A10]">
                        <div className="flex flex-col gap-1 min-w-0">
                            <div className="flex items-center gap-1">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" className="shrink-0">
                                    <g clipPath="url(#clip0_415_8014)">
                                        <path fillRule="evenodd" clipRule="evenodd" d="M2.99651 2.25C2.85476 2.25 2.72839 2.34825 2.67964 2.496L1.81414 5.12513C1.78681 5.20827 1.77289 5.29523 1.77289 5.38275V6.42863C1.77289 7.02 2.20451 7.5 2.73701 7.5C3.26951 7.5 3.70151 7.02 3.70151 6.42863C3.70151 7.02038 4.13314 7.5 4.66564 7.5C5.19814 7.5 5.63014 7.02 5.63014 6.42863C5.63014 7.02038 6.06176 7.5 6.59426 7.5C7.12676 7.5 7.55801 7.02075 7.55876 6.42938C7.55876 7.02075 7.99039 7.5 8.52289 7.5C9.05539 7.5 9.48701 7.02 9.48701 6.42863C9.48701 7.02038 9.91901 7.5 10.4515 7.5C10.984 7.5 11.4153 7.02075 11.4156 6.42938C11.416 7.02075 11.8476 7.5 12.3801 7.5C12.9126 7.5 13.3443 7.02 13.3443 6.42863C13.3443 7.02038 13.7759 7.5 14.3088 7.5C14.8413 7.5 15.2729 7.02 15.2729 6.42863V5.38275C15.2729 5.29523 15.259 5.20827 15.2316 5.12513L14.3661 2.49638C14.3174 2.34825 14.191 2.25 14.0493 2.25H2.99651Z" fill="white" />
                                        <path fillRule="evenodd" clipRule="evenodd" d="M3.27295 7.96113V10.8749H2.33545C2.28572 10.8749 2.23803 10.8946 2.20287 10.9298C2.1677 10.965 2.14795 11.0126 2.14795 11.0624V11.4374C2.14795 11.4871 2.1677 11.5348 2.20287 11.57C2.23803 11.6051 2.28572 11.6249 2.33545 11.6249H14.7104C14.7602 11.6249 14.8079 11.6051 14.843 11.57C14.8782 11.5348 14.8979 11.4871 14.8979 11.4374V11.0624C14.8979 11.0126 14.8782 10.965 14.843 10.9298C14.8079 10.8946 14.7602 10.8749 14.7104 10.8749H13.7729V7.96113C13.616 7.89857 13.471 7.80939 13.3443 7.6975C13.2471 7.78324 13.1391 7.85586 13.0229 7.9135V10.8749H4.02295V7.9135C3.90684 7.85586 3.79879 7.78324 3.70157 7.6975C3.57557 7.80813 3.43157 7.89813 3.27295 7.96113ZM13.0229 7.22725C13.0548 7.19538 13.0848 7.16125 13.1129 7.12488H13.0229V7.22725ZM13.5757 7.12488C13.6324 7.19833 13.6987 7.2638 13.7729 7.3195V7.12488H13.5757ZM3.27295 7.3195C3.34753 7.26423 3.41394 7.19871 3.4702 7.12488H3.27295V7.3195ZM3.93295 7.12488H4.02295V7.22725C3.99074 7.19514 3.96067 7.16094 3.93295 7.12488ZM2.71045 12.3749C2.66072 12.3749 2.61303 12.3946 2.57787 12.4298C2.5427 12.465 2.52295 12.5127 2.52295 12.5624V15.3749C2.52295 15.4743 2.56246 15.5697 2.63278 15.64C2.70311 15.7104 2.79849 15.7499 2.89795 15.7499H14.1479C14.2474 15.7499 14.3428 15.7104 14.4131 15.64C14.4834 15.5697 14.5229 15.4743 14.5229 15.3749V12.5624C14.5229 12.5127 14.5032 12.465 14.468 12.4298C14.4329 12.3946 14.3852 12.3749 14.3354 12.3749H2.71045Z" fill="white" />
                                        <path d="M4.77277 9.93762C4.77277 9.88789 4.79252 9.8402 4.82768 9.80504C4.86285 9.76988 4.91054 9.75012 4.96027 9.75012H6.08527C6.13499 9.75012 6.18269 9.76988 6.21785 9.80504C6.25301 9.8402 6.27277 9.88789 6.27277 9.93762V10.6876C6.27277 10.7374 6.25301 10.785 6.21785 10.8202C6.18269 10.8554 6.13499 10.8751 6.08527 10.8751H4.96027C4.91054 10.8751 4.86285 10.8554 4.82768 10.8202C4.79252 10.785 4.77277 10.7374 4.77277 10.6876V9.93762Z" fill="white" />
                                        <path d="M5.52295 10.3126C5.52295 10.2629 5.5427 10.2152 5.57787 10.18C5.61303 10.1449 5.66072 10.1251 5.71045 10.1251H6.83545C6.88518 10.1251 6.93287 10.1449 6.96803 10.18C7.00319 10.2152 7.02295 10.2629 7.02295 10.3126V10.6876C7.02295 10.7374 7.00319 10.785 6.96803 10.8202C6.93287 10.8554 6.88518 10.8751 6.83545 10.8751H5.71045C5.66072 10.8751 5.61303 10.8554 5.57787 10.8202C5.5427 10.785 5.52295 10.7374 5.52295 10.6876V10.3126ZM8.52295 10.3126C8.52295 10.4618 8.46369 10.6049 8.3582 10.7104C8.25271 10.8159 8.10963 10.8751 7.96045 10.8751C7.81126 10.8751 7.66819 10.8159 7.5627 10.7104C7.45721 10.6049 7.39795 10.4618 7.39795 10.3126C7.39795 10.1634 7.45721 10.0204 7.5627 9.91487C7.66819 9.80939 7.81126 9.75012 7.96045 9.75012C8.10963 9.75012 8.25271 9.80939 8.3582 9.91487C8.46369 10.0204 8.52295 10.1634 8.52295 10.3126Z" fill="white" />
                                    </g>
                                    <defs><clipPath id="clip0_415_8014"><rect width="18" height="18" rx="3.85714" fill="white" /></clipPath></defs>
                                </svg>
                                <span className="text-white font-gotham font-medium leading-[104%] text-[20px] 1024:text-[27px] whitespace-nowrap">
                                    Рынок скинов
                                </span>
                            </div>
                            <span className="text-[12px] font-sf-display leading-[104%] text-[#9C9DA9] hidden 1024:block">
                                Текст любой какой-то лялял и ляляля
                            </span>
                        </div>

                        <div className="flex items-center gap-2 ml-auto">
                            {/* Поиск — всегда видим */}
                            <div className="flex py-2 px-3 rounded-[8px] bg-[#0A0E17] items-center gap-1 w-[140px] 1024:w-[240px]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M5.25 1.5C3.18 1.5 1.5 3.18 1.5 5.25C1.5 7.32 3.18 9 5.25 9C7.32 9 9 7.32 9 5.25C9 3.18 7.32 1.5 5.25 1.5ZM10.5 10.5L8.25 8.25" stroke="#313743" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Поиск..."
                                    className="bg-transparent outline-none text-white font-sf-display text-[12px] leading-[104%] w-full placeholder:text-[#313743]"
                                />
                            </div>

                            {/* Кнопка фильтров */}
                            <button
                                onClick={() => setFiltersOpen(true)}
                                className="flex py-2 px-3 rounded-[8px] bg-[#0A0E17] items-center gap-1 cursor-pointer hover:bg-[#0F1520] transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M2.33301 3.5H11.6663M3.49967 7H10.4997M4.66634 10.5H9.33301" stroke="#4E89FF" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                                <span className="text-[#4E89FF] font-sf-display text-[11px] hidden 550:inline">Фильтры</span>
                            </button>

                            {/* Отменить + Корзина */}
                            <div className="flex items-center gap-[22px]">
                                <button
                                    onClick={() => setSelected(new Set())}
                                    className="font-sf-display text-[10px] leading-[120%] text-[#20242D] cursor-pointer hover:text-white/30 hidden 1024:block"
                                >
                                    Отменить все <br />
                                    выделенное
                                </button>
                                <button
                                    onClick={() => setCartOpen(true)}
                                    style={{ background: "linear-gradient(90deg, #FE7A02 0%, #FE4D00 100%)" }}
                                    className="relative w-full max-w-[120px] flex rounded-[12px] py-[7px] pl-2.5 pr-[52px] cursor-pointer hover:brightness-110 active:scale-[0.98] transition-all"
                                >
                                    <div className="flex items-start flex-col">
                                        <span className="text-[10px] leading-[120%] text-white">
                                            {selected.size > 0 ? `${selected.size} шт.` : '0 шт.'}
                                        </span>
                                        <span className="font-sf-display text-sm leading-[120%] text-white font-medium whitespace-nowrap">
                                            {formatKopecks(totalSelected)}
                                        </span>
                                    </div>
                                    <img src="/assets/img/bucket.svg" className="absolute bottom-0 right-0" alt="" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Грид скинов */}
                    <div
                        ref={scrollRef}
                        onScroll={handleScroll}
                        className="flex-1 overflow-y-auto skins-scroll p-2.5 bg-[#070A10] max-h-[calc(100vh-80px)]"
                    >
                        <div className="grid gap-1 grid-cols-[repeat(auto-fill,160px)] 1024:grid-cols-[repeat(auto-fill,200px)] justify-center">
                            {items.map((skin) => (
                                <SkinCard
                                    key={skin.id}
                                    {...skin}
                                    selected={selected.has(skin.id)}
                                    dimmed={selected.size > 0 && !selected.has(skin.id)}
                                    onClick={() => toggleSelect(skin.id)}
                                />
                            ))}
                        </div>

                        {loading && (
                            <div className="flex justify-center py-4">
                                <span className="text-white/20 font-sf-display text-[13px] animate-pulse">Загрузка...</span>
                            </div>
                        )}

                        {!loading && items.length === 0 && (
                            <div className="flex justify-center py-12">
                                <span className="text-white/20 font-sf-display text-[13px]">Скины не найдены</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Модалка корзины */}
            <Modal visible={cartOpen} onClose={() => setCartOpen(false)} maxWidth="max-w-[520px]">
                <div className="flex items-baseline gap-2">
                    <span className="text-white font-gotham font-medium text-xl leading-[100%]">Корзина</span>
                    <span className="text-white/40 font-sf-display text-[14px]">{selected.size} шт.</span>
                </div>

                {selectedItems.length > 0 ? (
                    <div className="flex gap-2 overflow-x-auto skins-scroll pb-2 -mx-[25px] px-[25px]">
                        {selectedItems.map((skin) => (
                            <div key={skin.id} className="shrink-0 relative">
                                <button
                                    onClick={() => toggleSelect(skin.id)}
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
                    onClick={() => { setSelected(new Set()); setCartOpen(false); }}
                    className="text-white/20 font-sf-display text-[13px] cursor-pointer hover:text-white/40 transition-colors text-center"
                >
                    Отменить все выделенное
                </button>

                <button
                    onClick={() => {
                        if (selected.size === 0) return;
                        setBuying(true);
                        const skinIds = selectedItems.map((s) => s.backendSkinId).filter(Boolean);
                        router.post('/market/buy', { skin_ids: skinIds }, {
                            preserveScroll: true,
                            onSuccess: () => { setSelected(new Set()); setCartOpen(false); },
                            onFinish: () => setBuying(false),
                        });
                    }}
                    disabled={selected.size === 0 || buying}
                    style={{ background: selected.size > 0 ? 'radial-gradient(80.57% 100% at 50% 100%, #4F86F5 0%, #05F 100%)' : undefined }}
                    className={`w-full py-4 rounded-[76px] flex justify-center items-center transition-all duration-200 ${
                        selected.size > 0
                            ? 'cursor-pointer hover:brightness-110 active:scale-[0.98]'
                            : 'bg-white/5 opacity-40 cursor-not-allowed'
                    }`}
                >
                    <span className="text-white font-sf-display text-[16px] font-medium leading-[120%]">
                        {buying ? 'Покупка...' : `${formatKopecks(totalSelected)}  |  Приобрести`}
                    </span>
                </button>
            </Modal>

            {/* Модалка фильтров */}
            <Modal visible={filtersOpen} onClose={() => setFiltersOpen(false)} maxWidth="max-w-[360px]">
                <span className="text-white font-gotham font-medium text-lg leading-[100%]">Фильтры</span>
                <div className="flex flex-col gap-3">
                    {filterControls}
                </div>
                <button
                    onClick={() => setFiltersOpen(false)}
                    style={{ background: "radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)" }}
                    className="w-full py-3 rounded-[12px] flex justify-center items-center cursor-pointer hover:brightness-125 active:scale-[0.98] transition-all"
                >
                    <span className="text-white font-sf-display text-[13px] font-medium">Применить</span>
                </button>
            </Modal>
        </AppLayout>
    );
}
