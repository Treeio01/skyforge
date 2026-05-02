import Button from '@/Components/UI/Button';
import Modal from '@/Components/UI/Modal';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

interface SellModalProps {
    visible: boolean;
    onClose: () => void;
    mode: 'all' | 'selected';
    selectedIds: Set<number>;
    onSuccess: () => void;
}

export default function SellModal({ visible, onClose, mode, selectedIds, onSuccess }: SellModalProps) {
    const { t } = useTranslation();
    const form = useForm({
        mode,
        ids: mode === 'selected' ? Array.from(selectedIds) : [] as number[],
    });

    const handleSell = () => {
        form.post(route('profile.sell-skins'), {
            preserveScroll: true,
            onSuccess: () => {
                onSuccess();
                onClose();
            },
        });
    };

    return (
        <Modal visible={visible} onClose={onClose}>
            <div className="flex flex-col gap-1">
                <span className="text-white font-gotham font-medium text-xl leading-[100%]">
                    {mode === 'all'
                        ? t('profile.sell_all_question')
                        : t('profile.sell_count_question', { count: selectedIds.size })}
                </span>
                <p className="font-sf-display text-[13px] leading-[140%] text-white/40">
                    {mode === 'all'
                        ? t('profile.sell_all_hint')
                        : t('profile.sell_count_hint', { count: selectedIds.size })}
                </p>
            </div>
            <div className="flex gap-2">
                <Button
                    variant="primary"
                    loading={form.processing}
                    onClick={handleSell}
                    className="flex-1"
                >
                    {t('profile.sell_button')}
                </Button>
                <button
                    onClick={onClose}
                    className="flex-1 py-3 rounded-[10px] bg-white/5 hover:bg-white/10 text-white/60 font-sf-display text-[13px] font-medium cursor-pointer transition-colors"
                >
                    {t('profile.sell_modal_cancel')}
                </button>
            </div>
        </Modal>
    );
}
