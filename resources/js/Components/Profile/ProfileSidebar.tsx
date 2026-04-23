import { formatKopecks } from '@/utils/skinHelpers';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import DepositHistoryModal from './DepositHistoryModal';

interface Profile {
    id: number;
    username: string;
    avatar_url: string | null;
    steam_id: string;
    balance: number;
    trade_url: string | null;
}

interface ProfileSidebarProps {
    profile: Profile;
}

export default function ProfileSidebar({ profile }: ProfileSidebarProps) {
    const [depositHistoryVisible, setDepositHistoryVisible] = useState(false);

    const tradeForm = useForm({ trade_url: profile.trade_url || '' });
    const promoForm = useForm({ code: '' });

    const handleTradeUrl = (e: React.FormEvent) => {
        e.preventDefault();
        tradeForm.put('/profile/trade-url');
    };

    const handlePromo = () => {
        if (!promoForm.data.code.trim()) return;
        promoForm.post(route('profile.promo'), {
            preserveScroll: true,
            onSuccess: () => promoForm.reset(),
        });
    };

    return (
        <div className="flex flex-col gap-3 w-full 1024:max-w-[254px]">
            <div className="flex flex-col gap-5">
                {/* Avatar + name + balance */}
                <div className="flex gap-2 items-center">
                    <img
                        src={profile.avatar_url || ''}
                        className="rounded-[12px] w-[40px] h-[40px]"
                        alt=""
                    />
                    <div className="flex flex-col">
                        <div className="flex gap-1 items-center">
                            <span className="font-sf-display text-[13px] leading-[120%] truncate max-w-[140px]">
                                {profile.username}
                            </span>
                            <div className="flex py-[2px] px-[5px] rounded-[49px] bg-[#BED4FF]/2">
                                <span className="font-sf-display font-light text-[10px] text-[#3E424A] leading-[120%]">
                                    {profile.steam_id.slice(-6)}
                                </span>
                            </div>
                        </div>
                        <span className="text-white font-gotham text-[13px] font-bold leading-[120%]">
                            {formatKopecks(profile.balance)}
                        </span>
                    </div>
                </div>

                {/* Trade URL */}
                <div className="flex items-center gap-1 w-full flex-col">
                    <form onSubmit={handleTradeUrl} className="flex flex-col gap-1 w-full">
                        <div className="flex py-3 px-3.5 w-full items-center justify-between border border-[#23262C] rounded-[8px] gap-2">
                            <input
                                type="text"
                                value={tradeForm.data.trade_url}
                                onChange={(e) => tradeForm.setData('trade_url', e.target.value)}
                                className="font-sf-display w-full outline-none text-[13px] leading-[120%]"
                                placeholder="Трейд-ссылка"
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={tradeForm.processing}
                            style={{
                                background: 'radial-gradient(41.52% 77.5% at 50.57% 100%, #4F86F5 0%, #05F 100%)',
                            }}
                            className="gap-[2px] w-full flex justify-center rounded-[8px] py-3 px-3.5 cursor-pointer"
                        >
                            <span className="text-white font-sf-display text-[13px] font-medium leading-[120%]">
                                Сохранить
                            </span>
                        </button>
                    </form>
                    <button
                        onClick={() => setDepositHistoryVisible(true)}
                        className="font-medium text-[10px] leading-[120%] font-sf-display text-[#2F3644] hover:text-[#4E89FF] transition-colors cursor-pointer"
                    >
                        История пополнений
                    </button>
                </div>
            </div>

            {/* Promo code */}
            <div
                style={{
                    background:
                        'radial-gradient(144.07% 72.57% at 89.37% -11.75%, rgba(255, 255, 255, 0.93) 0%, rgba(0, 191, 108, 0.00) 52.13%), linear-gradient(110deg, #00BF6C 36.74%, #0B6613 99.95%)',
                }}
                className="flex p-3 rounded-[12px] w-full gap-[10px] flex-col"
            >
                <span className="text-white font-gotham font-medium text-[17px] leading-[120%]">
                    Нашли промокод?
                </span>
                <div className="flex flex-col gap-1 w-full">
                    <div className="flex w-full py-3 px-3.5 rounded-[8px] border border-white/27">
                        <input
                            type="text"
                            value={promoForm.data.code}
                            onChange={(e) => promoForm.setData('code', e.target.value)}
                            className="text-white/24 leading-[120%] font-sf-display text-[13px] outline-none bg-transparent w-full"
                            placeholder="Введите промокод"
                        />
                    </div>
                    <button
                        onClick={handlePromo}
                        disabled={promoForm.processing}
                        style={{
                            background:
                                'radial-gradient(41.52% 77.5% at 50.57% 100%, rgba(255, 255, 255, 0.48) 0%, rgba(255, 255, 255, 0.14) 100%)',
                        }}
                        className="py-3 px-3.5 rounded-[8px] flex justify-center cursor-pointer disabled:opacity-50"
                    >
                        <span className="text-white font-sf-display text-[13px] leading-[120%] font-medium">
                            {promoForm.processing ? 'Проверяем...' : 'Применить'}
                        </span>
                    </button>
                </div>
            </div>

            <DepositHistoryModal
                visible={depositHistoryVisible}
                onClose={() => setDepositHistoryVisible(false)}
            />
        </div>
    );
}
