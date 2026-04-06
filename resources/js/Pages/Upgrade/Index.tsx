import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps, type Skin } from '@/types';
import { useState } from 'react';

interface InventoryItem {
    id: number;
    skin: Skin;
    price_at_acquisition: number;
}

interface Props extends PageProps {
    inventory: InventoryItem[];
    balance: number;
}

function formatPrice(kopecks: number): string {
    return (kopecks / 100).toLocaleString('ru-RU', { minimumFractionDigits: 2 }) + ' \u20BD';
}

export default function UpgradeIndex() {
    const { inventory, balance } = usePage<Props>().props;

    const [selectedSkins, setSelectedSkins] = useState<number[]>([]);
    const [balanceAmount, setBalanceAmount] = useState(0);
    const [targetSkin, setTargetSkin] = useState<Skin | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Skin[]>([]);
    const [searching, setSearching] = useState(false);

    const form = useForm({
        user_skin_ids: [] as number[],
        balance_amount: 0,
        target_skin_id: 0,
    });

    const skinsTotal = inventory
        .filter(i => selectedSkins.includes(i.id))
        .reduce((sum, i) => sum + i.price_at_acquisition, 0);

    const betTotal = skinsTotal + balanceAmount;
    const maxBalance = Math.min(balance, (targetSkin?.price ?? 0) - skinsTotal - 1);

    const chance = targetSkin && betTotal > 0 && betTotal < targetSkin.price
        ? Math.min(95, Math.max(1, (betTotal / targetSkin.price) * 95))
        : 0;

    function toggleSkin(id: number) {
        setSelectedSkins(prev =>
            prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
        );
    }

    async function searchSkins(q: string) {
        setSearchQuery(q);
        if (q.length < 2) {
            setSearchResults([]);
            return;
        }
        setSearching(true);
        try {
            const res = await fetch(`/api/skins/search?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            setSearchResults(data.data || []);
        } finally {
            setSearching(false);
        }
    }

    function submitUpgrade() {
        if (!targetSkin || betTotal <= 0 || betTotal >= targetSkin.price) return;

        form.transform(() => ({
            user_skin_ids: selectedSkins,
            balance_amount: balanceAmount,
            target_skin_id: targetSkin.id,
        }));

        form.post(route('upgrade.store'), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="Апгрейд" />

            <div className="max-w-7xl mx-auto px-4 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {/* Left: Bet panel */}
                    <div className="bg-card border border-border rounded-lg p-5">
                        <h2 className="text-lg font-bold mb-4">Ставка</h2>

                        {/* Inventory */}
                        <div className="mb-4">
                            <h3 className="text-sm text-[#888] mb-2">Мой инвентарь ({inventory.length})</h3>
                            {inventory.length === 0 ? (
                                <p className="text-sm text-[#555]">Нет скинов. Пополните баланс и купите скины.</p>
                            ) : (
                                <div className="grid grid-cols-3 gap-2 max-h-64 overflow-y-auto">
                                    {inventory.map(item => (
                                        <button
                                            key={item.id}
                                            onClick={() => toggleSkin(item.id)}
                                            className={`relative border rounded p-2 text-center text-xs transition-colors ${
                                                selectedSkins.includes(item.id)
                                                    ? 'border-accent bg-accent-dim'
                                                    : 'border-border hover:border-border-hover'
                                            }`}
                                        >
                                            {item.skin.image_url && (
                                                <img src={item.skin.image_url} alt="" className="w-full h-12 object-contain mb-1" />
                                            )}
                                            <div className="truncate">{item.skin.market_hash_name}</div>
                                            <div className="text-accent text-[10px]">{formatPrice(item.price_at_acquisition)}</div>
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Balance slider */}
                        <div className="mb-4">
                            <h3 className="text-sm text-[#888] mb-2">Добавить баланс</h3>
                            <input
                                type="range"
                                min={0}
                                max={Math.max(0, maxBalance)}
                                step={100}
                                value={balanceAmount}
                                onChange={e => setBalanceAmount(Number(e.target.value))}
                                className="w-full accent-[#a3e635]"
                            />
                            <div className="text-sm text-accent mt-1">{formatPrice(balanceAmount)}</div>
                        </div>

                        {/* Total */}
                        <div className="border-t border-border pt-3">
                            <div className="flex justify-between text-sm">
                                <span className="text-[#888]">Итого ставка:</span>
                                <span className="font-bold text-accent">{formatPrice(betTotal)}</span>
                            </div>
                        </div>
                    </div>

                    {/* Center: Chance ring + action */}
                    <div className="bg-card border border-border rounded-lg p-5 flex flex-col items-center justify-center">
                        {/* Chance ring */}
                        <div
                            className="relative w-48 h-48 rounded-full flex items-center justify-center mb-6"
                            style={{
                                background: `conic-gradient(#a3e635 ${chance * 3.6}deg, #1e1e1e ${chance * 3.6}deg)`,
                            }}
                        >
                            <div className="w-40 h-40 rounded-full bg-card flex items-center justify-center">
                                <div className="text-center">
                                    <div className="text-3xl font-extrabold text-accent">{chance.toFixed(1)}%</div>
                                    <div className="text-xs text-[#888]">шанс</div>
                                </div>
                            </div>
                        </div>

                        {targetSkin && (
                            <div className="text-center mb-4">
                                <div className="text-sm text-[#888]">Множитель</div>
                                <div className="text-2xl font-bold">
                                    x{betTotal > 0 ? (targetSkin.price / betTotal).toFixed(2) : '0'}
                                </div>
                            </div>
                        )}

                        <button
                            onClick={submitUpgrade}
                            disabled={form.processing || !targetSkin || betTotal <= 0 || betTotal >= (targetSkin?.price ?? 0)}
                            className="w-full rounded-lg bg-accent px-6 py-4 text-lg font-extrabold text-[#0a0a0a] hover:bg-accent-hover transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                        >
                            {form.processing ? 'Крутим...' : 'UPGRADE'}
                        </button>
                    </div>

                    {/* Right: Target skin */}
                    <div className="bg-card border border-border rounded-lg p-5">
                        <h2 className="text-lg font-bold mb-4">Цель</h2>

                        {/* Search */}
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={e => searchSkins(e.target.value)}
                            placeholder="Поиск скина..."
                            className="w-full bg-[#0a0a0a] border border-border rounded-lg px-3 py-2 text-sm text-[#f5f5f5] placeholder-[#555] focus:border-accent focus:outline-none mb-3"
                        />

                        {/* Selected target */}
                        {targetSkin && (
                            <div className="border border-accent rounded-lg p-3 mb-3 bg-accent-dim">
                                <div className="flex items-center gap-3">
                                    {targetSkin.image_url && (
                                        <img src={targetSkin.image_url} alt="" className="w-16 h-16 object-contain" />
                                    )}
                                    <div>
                                        <div className="text-sm font-medium truncate">{targetSkin.market_hash_name}</div>
                                        <div className="text-accent font-bold">{formatPrice(targetSkin.price)}</div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Search results */}
                        <div className="space-y-1 max-h-80 overflow-y-auto">
                            {searching && <p className="text-sm text-[#555]">Поиск...</p>}
                            {searchResults.map(skin => (
                                <button
                                    key={skin.id}
                                    onClick={() => {
                                        setTargetSkin(skin);
                                        setSearchQuery('');
                                        setSearchResults([]);
                                    }}
                                    className={`w-full flex items-center gap-3 border rounded-lg p-2 text-left transition-colors ${
                                        targetSkin?.id === skin.id
                                            ? 'border-accent bg-accent-dim'
                                            : 'border-border hover:border-border-hover'
                                    }`}
                                >
                                    {skin.image_url && (
                                        <img src={skin.image_url} alt="" className="w-10 h-10 object-contain flex-shrink-0" />
                                    )}
                                    <div className="min-w-0 flex-1">
                                        <div className="text-xs truncate">{skin.market_hash_name}</div>
                                        <div className="text-accent text-xs font-bold">{formatPrice(skin.price)}</div>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
