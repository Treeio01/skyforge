import Modal from '@/Components/UI/Modal';
import { formatKopecks } from '@/utils/skinHelpers';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';

interface Deposit {
    id: number;
    amount: number;
    balance_after: number;
    description: string | null;
    created_at: string;
}

interface DepositHistoryModalProps {
    visible: boolean;
    onClose: () => void;
}

export default function DepositHistoryModal({ visible, onClose }: DepositHistoryModalProps) {
    const { t, i18n } = useTranslation();
    const [deposits, setDeposits] = useState<Deposit[]>([]);
    const [loading, setLoading] = useState(false);
    const [loaded, setLoaded] = useState(false);

    useEffect(() => {
        if (visible && !loaded) {
            setLoading(true);
            axios.get('/profile/deposits')
                .then((res) => {
                    setDeposits(res.data);
                    setLoaded(true);
                })
                .finally(() => setLoading(false));
        }
    }, [visible, loaded]);

    const formatDate = (iso: string) => {
        const d = new Date(iso);
        const localeTag = i18n.language === 'en' ? 'en-US' : 'ru-RU';
        return d.toLocaleDateString(localeTag, { day: '2-digit', month: '2-digit', year: 'numeric' })
            + ' ' + d.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
    };

    return (
        <Modal visible={visible} onClose={onClose} maxWidth="max-w-[460px]">
            <div className="flex flex-col gap-1">
                <span className="text-white font-gotham font-medium text-xl leading-[100%]">
                    {t('deposit.history_title')}
                </span>
                <p className="font-sf-display text-[13px] leading-[140%] text-white/40">
                    {t('deposit.history_subtitle')}
                </p>
            </div>

            <div className="flex flex-col gap-1 max-h-[400px] overflow-y-auto custom-scrollbar">
                {loading && (
                    <div className="flex items-center justify-center h-[80px]">
                        <span className="text-white/20 font-sf-display text-[13px] animate-pulse">{t('common.loading')}</span>
                    </div>
                )}

                {!loading && deposits.length === 0 && (
                    <div className="flex items-center justify-center h-[80px]">
                        <span className="text-white/20 font-sf-display text-[13px]">{t('deposit.no_deposits')}</span>
                    </div>
                )}

                {deposits.map((d) => (
                    <div
                        key={d.id}
                        className="flex items-center justify-between p-3 rounded-[10px] bg-white/2 hover:bg-white/4 transition-colors"
                    >
                        <div className="flex flex-col gap-0.5">
                            <span className="text-white font-sf-display text-[13px] leading-[120%]">
                                +{formatKopecks(d.amount)}
                            </span>
                            <span className="text-white/20 font-sf-display text-[10px] leading-[120%]">
                                {formatDate(d.created_at)}
                            </span>
                        </div>
                        <div className="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M6 1L2 5H5V11H7V5H10L6 1Z" fill="#00BF6C" />
                            </svg>
                            <span className="text-[#00BF6C] font-sf-display text-[11px] leading-[120%]">
                                {t('deposit.deposit_label')}
                            </span>
                        </div>
                    </div>
                ))}
            </div>
        </Modal>
    );
}
