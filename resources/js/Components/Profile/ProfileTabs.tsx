import SkinCard, { ItemBackgroundLines } from '@/Components/Upgrade/SkinCard';
import { formatKopecks, mapRarityColor, parseSkinName, inventoryItemToEntry } from '@/utils/skinHelpers';
import { Skin } from '@/types';
import { useState } from 'react';

type Tab = 'inventory' | 'history' | 'upgrades';

interface InventoryItem {
    id: number;
    skin: Skin;
    price_at_acquisition: number;
}

interface UpgradeEntry {
    id: number;
    target_skin_name: string;
    target_skin_image: string | null;
    target_skin_rarity_color: string | null;
    bet_skin_name: string | null;
    bet_skin_image: string | null;
    bet_skin_rarity_color: string | null;
    target_price: number;
    bet_amount: number;
    chance: number;
    result: 'win' | 'lose';
    created_at: string;
}

interface ProfileTabsProps {
    inventory: InventoryItem[];
    recentUpgrades: UpgradeEntry[];
    selectedSkins: Set<number>;
    onToggleSkin: (id: number) => void;
    onSellAll: () => void;
    onSellSelected: () => void;
}

export default function ProfileTabs({
    inventory, recentUpgrades, selectedSkins, onToggleSkin, onSellAll, onSellSelected,
}: ProfileTabsProps) {
    const [activeTab, setActiveTab] = useState<Tab>('inventory');

    return (
        <div className="flex p-4 1024:p-6 flex-col w-full gap-3 bg-[#11161F] rounded-[14px]">
            <div className="flex w-full items-center justify-between">
                <div className="flex items-center gap-1">
                    <TabButton active={activeTab === 'inventory'} onClick={() => setActiveTab('inventory')} icon={<InventoryTabIcon active={activeTab === 'inventory'} />} label="Инвентарь" />
                    <TabButton active={activeTab === 'history'} onClick={() => setActiveTab('history')} icon={<HistoryTabIcon active={activeTab === 'history'} />} label="История" />
                    <TabButton active={activeTab === 'upgrades'} onClick={() => setActiveTab('upgrades')} icon={<UpgradesTabIcon active={activeTab === 'upgrades'} />} label="Апгрейды" />
                </div>
                {activeTab === 'inventory' && (
                    <div className="flex gap-1.5">
                        {selectedSkins.size > 0 && (
                            <button
                                onClick={onSellSelected}
                                className="p-3.5 flex gap-1.5 items-center rounded-[12px] cursor-pointer transition-colors duration-200 bg-[#1A2A3D] hover:bg-[#23375A] text-[#A0C4FF] hover:text-white"
                            >
                                <SellIcon fill="currentColor" />
                                <span className="font-sf-display text-[13px] font-light leading-[120%] hidden 550:inline">
                                    Продать ({selectedSkins.size})
                                </span>
                            </button>
                        )}
                        <button
                            onClick={onSellAll}
                            className="p-3.5 flex gap-1.5 items-center rounded-[12px] cursor-pointer transition-colors duration-200 text-white/45 hover:bg-white/4 hover:text-white/80"
                        >
                            <SellIcon fill="currentColor" />
                            <span className="font-sf-display text-[13px] font-light leading-[120%] hidden 550:inline">
                                Продать все
                            </span>
                        </button>
                    </div>
                )}
            </div>

            <div className={activeTab === 'upgrades' ? 'flex flex-wrap gap-2 justify-center' : 'grid grid-cols-[repeat(auto-fill,minmax(130px,1fr))] 1024:grid-cols-[repeat(auto-fill,minmax(140px,1fr))] gap-[4px]'}>
                {activeTab === 'inventory' && (
                    inventory.length === 0 ? (
                        <EmptyState text="Нет скинов" />
                    ) : (
                        inventory.map((item) => {
                            const entry = inventoryItemToEntry(item);
                            const isSelected = selectedSkins.has(item.id);
                            return (
                                <SkinCard
                                    key={entry.id}
                                    {...entry}
                                    selected={isSelected}
                                    dimmed={selectedSkins.size > 0 && !isSelected}
                                    onClick={() => onToggleSkin(item.id)}
                                />
                            );
                        })
                    )
                )}

                {activeTab === 'history' && (
                    recentUpgrades.length === 0 ? (
                        <EmptyState text="Нет истории" />
                    ) : (
                        recentUpgrades.map((u) => (
                            <div key={u.id} className={u.result === 'lose' ? 'opacity-50' : ''}>
                                <SkinCard
                                    id={u.id}
                                    rarity={mapRarityColor(u.target_skin_rarity_color)}
                                    weapon={parseSkinName(u.target_skin_name).weapon}
                                    name={parseSkinName(u.target_skin_name).name}
                                    price={formatKopecks(u.target_price)}
                                    priceKopecks={u.target_price}
                                    image={u.target_skin_image || ''}
                                />
                            </div>
                        ))
                    )
                )}

                {activeTab === 'upgrades' && (
                    recentUpgrades.length === 0 ? (
                        <EmptyState text="Нет апгрейдов" />
                    ) : (
                        recentUpgrades.map((u) => (
                            <UpgradeHistoryCard key={u.id} upgrade={u} />
                        ))
                    )
                )}
            </div>
        </div>
    );
}

function UpgradeHistoryCard({ upgrade: u }: { upgrade: UpgradeEntry }) {
    const tgtRarity = mapRarityColor(u.target_skin_rarity_color);
    const betRarity = mapRarityColor(u.bet_skin_rarity_color);
    const isWin = u.result === 'win';

    return (
        <div className={`flex p-2.5 gap-[5px] rounded-[12px] transition-opacity duration-200 bg-[#161B26] ${u.result === 'lose' ? 'opacity-60' : ''}`}>
            <div className="flex flex-col gap-[5px]">
                <div
                    className={`rarity-${betRarity} flex items-center justify-center w-[138px] h-[75px] rounded-[10px] bg-[#BED4FF]/2 bg-linear-to-b from-transparent to-[var(--rarity-from)] relative overflow-hidden`}
                    style={{ borderBottom: '2px solid var(--rarity-accent)' }}
                >
                    <ItemBackgroundLines />
                    {u.bet_skin_image && (
                        <img src={u.bet_skin_image} alt="" className="w-[80px] h-[55px] object-contain relative z-10" />
                    )}
                </div>
                <div className="flex justify-center p-2 bg-[#11161F] rounded-[8px]">
                    <span className="text-white/60 font-sf-display text-[11px] leading-[120%] whitespace-nowrap">
                        {formatKopecks(u.bet_amount)}
                    </span>
                </div>
            </div>
            <div className="flex flex-col gap-[5px]">
                <div
                    className={`rarity-${tgtRarity} flex items-center justify-center w-[138px] h-[75px] rounded-[10px] bg-[#BED4FF]/2 bg-linear-to-b from-transparent to-[var(--rarity-from)] relative overflow-hidden`}
                    style={{ borderBottom: '2px solid var(--rarity-accent)' }}
                >
                    <ItemBackgroundLines />
                    {u.target_skin_image && (
                        <img src={u.target_skin_image} alt="" className="w-[80px] h-[55px] object-contain relative z-10" />
                    )}
                </div>
                <div className={`flex justify-center p-2 rounded-[8px] ${isWin ? 'bg-[#1A2A3D]' : 'bg-[#11161F]'}`}>
                    <span className={`font-sf-display text-[11px] leading-[120%] whitespace-nowrap ${isWin ? 'text-[#A0C4FF]' : 'text-white/60'}`}>
                        {formatKopecks(u.target_price)}
                    </span>
                </div>
            </div>
        </div>
    );
}

function EmptyState({ text }: { text: string }) {
    return (
        <div className="flex items-center justify-center w-full h-[100px]">
            <span className="text-white/20 font-sf-display text-[13px]">{text}</span>
        </div>
    );
}

function TabButton({ active, onClick, icon, label }: { active: boolean; onClick: () => void; icon: React.ReactNode; label: string }) {
    return (
        <button
            onClick={onClick}
            className={`p-3.5 flex gap-1.5 items-center rounded-[12px] cursor-pointer transition-colors duration-200 ${active ? 'bg-[#1A2030] text-white' : 'text-white/45 hover:bg-white/4 hover:text-white/85'}`}
        >
            {icon}
            <span className="font-sf-display text-[13px] leading-[120%] hidden 550:inline">
                {label}
            </span>
        </button>
    );
}

function SellIcon({ fill }: { fill: string }) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M12.4895 6.65591L7.33866 1.50508C7.12283 1.28925 6.82533 1.16675 6.51616 1.16675H2.33366C1.69199 1.16675 1.16699 1.69175 1.16699 2.33341V6.51591C1.16699 6.82508 1.28949 7.12258 1.51116 7.33842L6.66199 12.4892C7.11699 12.9442 7.85783 12.9442 8.31283 12.4892L12.4953 8.30675C12.9503 7.85175 12.9503 7.11675 12.4895 6.65591ZM3.79199 4.66675C3.30783 4.66675 2.91699 4.27591 2.91699 3.79175C2.91699 3.30758 3.30783 2.91675 3.79199 2.91675C4.27616 2.91675 4.66699 3.30758 4.66699 3.79175C4.66699 4.27591 4.27616 4.66675 3.79199 4.66675Z" fill={fill} />
        </svg>
    );
}

function InventoryTabIcon({ active }: { active: boolean }) {
    const fill = active ? 'white' : 'rgba(255,255,255,0.45)';
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="11" viewBox="0 0 13 11" fill="none">
            <g clipPath="url(#ci)"><path d="M11.3438 1.20312C11.3438 0.917383 11.1139 0.6875 10.8281 0.6875C10.5424 0.6875 10.3125 0.917383 10.3125 1.20312V1.375H0.6875C0.307227 1.375 0 1.68223 0 2.0625V4.46875C0 4.84902 0.307227 5.15625 0.6875 5.15625H0.902344C1.34922 5.15625 1.67793 5.57734 1.56836 6.01133L0.708984 9.45742C0.657422 9.66367 0.704687 9.88066 0.833594 10.0482C0.9625 10.2158 1.1623 10.3125 1.375 10.3125H3.4375C3.75332 10.3125 4.02832 10.0977 4.10352 9.79258L4.66211 7.5625H6.90508C7.41426 7.5625 7.86758 7.24238 8.0373 6.76328L8.61309 5.15625H9.28125C9.46387 5.15625 9.63789 5.0832 9.7668 4.9543L10.2545 4.46875H11.6875C12.0678 4.46875 12.375 4.16152 12.375 3.78125V2.0625C12.375 1.68223 12.0678 1.375 11.6875 1.375H11.3438V1.20312ZM6.90508 6.53125H4.91992L5.26367 5.15625H7.51953L7.06836 6.41738C7.04473 6.48613 6.97813 6.53125 6.90723 6.53125H6.90508ZM1.71875 2.75H9.96875C10.1578 2.75 10.3125 2.90469 10.3125 3.09375C10.3125 3.28281 10.1578 3.4375 9.96875 3.4375H1.71875C1.52969 3.4375 1.375 3.28281 1.375 3.09375C1.375 2.90469 1.52969 2.75 1.71875 2.75Z" fill={fill} /></g>
            <defs><clipPath id="ci"><rect width="12.375" height="11" fill="white" /></clipPath></defs>
        </svg>
    );
}

function HistoryTabIcon({ active }: { active: boolean }) {
    const fill = active ? 'white' : 'rgba(255,255,255,0.45)';
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <g clipPath="url(#ch)"><path d="M6.30005 4.19995V7.69995H6.34205L8.07805 9.42895L9.06505 8.44195L7.70005 7.07695V4.19995H6.30005Z" fill={fill} /><path d="M7.0001 0.699951C5.90616 0.703464 4.83198 0.99177 3.8833 1.53649C2.93461 2.08121 2.14412 2.86358 1.58962 3.80658C1.03512 4.74958 0.735728 5.82072 0.720907 6.91457C0.706087 8.00842 0.97635 9.08727 1.5051 10.045L0.350098 11.2H4.2001V7.34995L2.5341 9.01595C2.09889 8.05184 1.98736 6.97278 2.21617 5.94004C2.44498 4.9073 3.00184 3.97632 3.80354 3.28625C4.60523 2.59618 5.60871 2.18406 6.664 2.1115C7.7193 2.03893 8.76974 2.3098 9.65835 2.88364C10.547 3.45747 11.226 4.30345 11.594 5.29516C11.9621 6.28686 11.9993 7.37102 11.7002 8.38563C11.401 9.40024 10.7816 10.2908 9.93443 10.9242C9.08727 11.5577 8.05788 11.9 7.0001 11.9V13.3C8.67096 13.3 10.2734 12.6362 11.4549 11.4547C12.6363 10.2732 13.3001 8.67082 13.3001 6.99995C13.3001 5.32909 12.6363 3.72666 11.4549 2.54518C10.2734 1.3637 8.67096 0.699951 7.0001 0.699951Z" fill={fill} /></g>
            <defs><clipPath id="ch"><rect width="14" height="14" fill="white" /></clipPath></defs>
        </svg>
    );
}

function UpgradesTabIcon({ active }: { active: boolean }) {
    const fill = active ? 'white' : 'rgba(255,255,255,0.45)';
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <g clipPath="url(#cu)"><path d="M7 0.815308L0.683594 5.02625V7.93474L7 3.72381L13.3164 7.93474V5.02625L7 0.815308ZM7 4.31648L2.87109 7.10347V9.57214L7 6.78513L11.1289 9.57214V7.10347L7 4.31648ZM7 7.37898L4.62109 8.98475V10.8332L7 9.24724L9.37891 10.8332V8.98475L7 7.37898ZM7 9.83875L4.62109 11.4247V13.1847L7 11.5988L9.37891 13.1847V11.4247L7 9.83875Z" fill={fill} /></g>
            <defs><clipPath id="cu"><rect width="14" height="14" fill="white" /></clipPath></defs>
        </svg>
    );
}
