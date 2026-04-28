import { ReactNode } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface ModalProps {
    visible: boolean;
    onClose: () => void;
    children: ReactNode;
    maxWidth?: string;
}

const MODAL_STYLE = {
    border: '1px solid rgba(255, 255, 255, 0.21)',
    background:
        'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)',
    boxShadow: '0 26px 80px 0 rgba(0, 0, 0, 0.30)',
};

export default function Modal({ visible, onClose, children, maxWidth = 'max-w-[400px]' }: ModalProps) {
    return (
        <AnimatePresence>
            {visible && (
                <motion.div
                    className="flex z-[1000] items-end 1024:items-center justify-center fixed inset-0"
                    initial={{ opacity: 0, backdropFilter: 'blur(0px)' }}
                    animate={{ opacity: 1, backdropFilter: 'blur(8px)' }}
                    exit={{ opacity: 0, backdropFilter: 'blur(0px)' }}
                    transition={{ duration: 0.25, ease: [0.22, 1, 0.36, 1] }}
                    onClick={onClose}
                    style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}
                >
                    {/* Мобильный bottom-sheet */}
                    <motion.div
                        onClick={(e) => e.stopPropagation()}
                        style={MODAL_STYLE}
                        className={`flex 1024:hidden rounded-t-[20px] backdrop-blur-[70px] w-full p-[25px] gap-5 flex-col max-h-[85vh] overflow-y-auto`}
                        initial={{ y: '100%', opacity: 0.6 }}
                        animate={{ y: 0, opacity: 1 }}
                        exit={{ y: '100%', opacity: 0.6 }}
                        transition={{ type: 'spring', damping: 32, stiffness: 260, mass: 0.9 }}
                    >
                        <div className="flex justify-center pb-1">
                            <div className="w-[40px] h-[4px] rounded-full bg-white/20" />
                        </div>
                        {children}
                    </motion.div>

                    {/* Десктопная модалка */}
                    <motion.div
                        onClick={(e) => e.stopPropagation()}
                        style={MODAL_STYLE}
                        className={`hidden 1024:flex rounded-[20px] backdrop-blur-[70px] w-full ${maxWidth} p-[25px] gap-5 flex-col`}
                        initial={{ opacity: 0, scale: 0.94, y: 12 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.96, y: 8 }}
                        transition={{ type: 'spring', damping: 28, stiffness: 240, mass: 0.8 }}
                    >
                        {children}
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
