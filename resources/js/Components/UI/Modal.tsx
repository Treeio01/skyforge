import { ReactNode } from 'react';

interface ModalProps {
    visible: boolean;
    onClose: () => void;
    children: ReactNode;
    maxWidth?: string;
}

export default function Modal({
    visible,
    onClose,
    children,
    maxWidth = 'max-w-[400px]',
}: ModalProps) {
    return (
        <div
            className={`flex z-[1000] items-end 1024:items-center justify-center bg-black/40 fixed inset-0 transition-opacity duration-500 ease-out ${
                visible ? 'opacity-100' : 'opacity-0 pointer-events-none'
            }`}
            onClick={onClose}
        >
            {/* Мобильный bottom-sheet */}
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    border: '1px solid rgba(255, 255, 255, 0.21)',
                    background:
                        'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)',
                    boxShadow: '0 26px 80px 0 rgba(0, 0, 0, 0.30)',
                }}
                className={`flex 1024:hidden rounded-t-[20px] backdrop-blur-[70px] w-full p-[25px] gap-5 flex-col max-h-[85vh] overflow-y-auto transition-all duration-500 ease-out ${
                    visible
                        ? 'opacity-100 translate-y-0'
                        : 'opacity-0 translate-y-full'
                }`}
            >
                <div className="flex justify-center pb-1">
                    <div className="w-[40px] h-[4px] rounded-full bg-white/20" />
                </div>
                {children}
            </div>

            {/* Десктопная модалка */}
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    border: '1px solid rgba(255, 255, 255, 0.21)',
                    background:
                        'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)',
                    boxShadow: '0 26px 80px 0 rgba(0, 0, 0, 0.30)',
                }}
                className={`hidden 1024:flex rounded-[20px] backdrop-blur-[70px] w-full ${maxWidth} p-[25px] gap-5 flex-col transition-all duration-500 ease-out ${
                    visible
                        ? 'opacity-100 scale-100 translate-y-0'
                        : 'opacity-0 scale-95 translate-y-4'
                }`}
            >
                {children}
            </div>
        </div>
    );
}
