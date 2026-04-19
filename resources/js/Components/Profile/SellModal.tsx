import Modal from '@/Components/UI/Modal';
import GradientButton from '@/Components/UI/GradientButton';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface SellModalProps {
    visible: boolean;
    onClose: () => void;
    mode: 'all' | 'selected';
    selectedIds: Set<number>;
    onSuccess: () => void;
}

export default function SellModal({ visible, onClose, mode, selectedIds, onSuccess }: SellModalProps) {
    const [processing, setProcessing] = useState(false);

    const handleSell = () => {
        setProcessing(true);
        router.post('/profile/sell-skins', {
            mode,
            ids: mode === 'selected' ? Array.from(selectedIds) : [],
        }, {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess();
                onClose();
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <Modal visible={visible} onClose={onClose}>
            <div className="flex flex-col gap-1">
                <span className="text-white font-gotham font-medium text-xl leading-[100%]">
                    {mode === 'all' ? 'Продать все скины?' : `Продать ${selectedIds.size} скинов?`}
                </span>
                <p className="font-sf-display text-[13px] leading-[140%] text-white/40">
                    {mode === 'all'
                        ? 'Все ваши скины будут проданы по текущим ценам. Средства поступят на баланс.'
                        : `Выбранные скины (${selectedIds.size} шт.) будут проданы. Средства поступят на баланс.`}
                </p>
            </div>
            <div className="flex gap-2">
                <GradientButton className="flex-1" onClick={handleSell} disabled={processing}>
                    <span className="text-white font-sf-display text-[13px] font-medium">
                        {processing ? 'Продаём...' : 'Продать'}
                    </span>
                </GradientButton>
                <button
                    onClick={onClose}
                    className="flex-1 py-3 rounded-[10px] bg-white/5 hover:bg-white/10 text-white/60 font-sf-display text-[13px] font-medium cursor-pointer transition-colors"
                >
                    Отменить
                </button>
            </div>
        </Modal>
    );
}
