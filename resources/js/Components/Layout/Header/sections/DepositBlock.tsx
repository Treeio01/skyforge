import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { WalletIcon } from '@/Components/UI/Icons';
import { ChipLabel } from '../primitives';
import DepositModal from '@/Components/Deposit/DepositModal';
import { useState } from 'react';

export default function DepositBlock() {
    const user = usePage<PageProps>().props.auth.user;
    const [depositVisible, setDepositVisible] = useState(false);

    const balanceRubles = user
        ? (user.balance / 100).toLocaleString('ru-RU')
        : '0';

    return (
        <>
            <div className="flex rounded-[12px] gap-2.5 items-center">
                <div className="flex pl-3 py-3 gap-1 items-center bg-accent">
                    <ChipLabel>{balanceRubles}</ChipLabel>
                    <ChipLabel>₽</ChipLabel>
                </div>
                <button
                    type="button"
                    onClick={() => setDepositVisible(true)}
                    className="flex p-3 border-b-[2px] border-b-[#09368F] rounded-[12px] cursor-pointer transition-[filter] duration-150 hover:brightness-110"
                    style={{
                        background:
                            'radial-gradient(77.5% 77.5% at 50.57% 100%, #4F86F5 0%, #05F 100%)',
                    }}
                >
                    <WalletIcon />
                </button>
            </div>
            <DepositModal
                visible={depositVisible}
                onClose={() => setDepositVisible(false)}
            />
        </>
    );
}
