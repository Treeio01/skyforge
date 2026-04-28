import { ReactNode } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface BottomSheetProps {
    visible: boolean;
    onClose: () => void;
    title: string;
    headerRight?: ReactNode;
    children: ReactNode;
}

const SHEET_BG = {
    background: 'linear-gradient(180deg, #0D1525 0%, #080B10 100%)',
    borderTop: '1px solid rgba(255, 255, 255, 0.08)',
    borderLeft: '1px solid rgba(255, 255, 255, 0.06)',
    borderRight: '1px solid rgba(255, 255, 255, 0.06)',
};

export default function BottomSheet({ visible, onClose, title, headerRight, children }: BottomSheetProps) {
    return (
        <AnimatePresence>
            {visible && (
                <motion.div
                    className="fixed inset-0 z-[500] flex flex-col justify-end 1024:hidden"
                    initial={{ opacity: 0, backdropFilter: 'blur(0px)' }}
                    animate={{ opacity: 1, backdropFilter: 'blur(6px)' }}
                    exit={{ opacity: 0, backdropFilter: 'blur(0px)' }}
                    transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
                    onClick={onClose}
                >
                    <div className="absolute inset-0 bg-black/60" />

                    <motion.div
                        onClick={(e) => e.stopPropagation()}
                        style={SHEET_BG}
                        className="relative flex flex-col rounded-t-[24px] w-full h-[calc(100dvh-48px)]"
                        initial={{ y: '100%', opacity: 0.7 }}
                        animate={{ y: 0, opacity: 1 }}
                        exit={{ y: '100%', opacity: 0.7 }}
                        transition={{ type: 'spring', damping: 34, stiffness: 280, mass: 0.85 }}
                    >
                        {/* Ручка */}
                        <div className="flex justify-center pt-3 pb-1 shrink-0">
                            <div className="w-10 h-1 rounded-full bg-white/20" />
                        </div>

                        {/* Заголовок */}
                        <div className="flex items-center justify-between px-4 py-3 shrink-0">
                            <span className="text-white font-gotham font-medium text-[18px] leading-[104%]">
                                {title}
                            </span>
                            <div className="flex items-center gap-2">
                                {headerRight}
                                <button
                                    type="button"
                                    onClick={onClose}
                                    className="p-2 rounded-[8px] bg-white/6 hover:bg-white/12 transition-colors"
                                >
                                    <svg width="12" height="12" viewBox="0 0 10 10" fill="none">
                                        <path
                                            d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5"
                                            stroke="white"
                                            strokeOpacity="0.5"
                                            strokeLinecap="round"
                                            strokeWidth="1.2"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Контент */}
                        <div className="flex-1 min-h-0 overflow-y-auto skins-scroll px-3 pb-6">
                            {children}
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
