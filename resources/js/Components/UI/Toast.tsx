import { createContext, useContext, useState, useCallback, useEffect, useRef, ReactNode } from 'react';
import { router } from '@inertiajs/react';

type ToastType = 'success' | 'error' | 'info';

interface Toast {
    id: number;
    type: ToastType;
    message: string;
}

interface ToastContextValue {
    toast: (type: ToastType, message: string) => void;
}

const ToastContext = createContext<ToastContextValue>({ toast: () => {} });

export const useToast = () => useContext(ToastContext);

let nextId = 0;

export function ToastProvider({ children }: { children: ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);
    const addToastRef = useRef<(type: ToastType, message: string) => void>();

    const addToast = useCallback((type: ToastType, message: string) => {
        const id = ++nextId;
        setToasts((prev) => [...prev, { id, type, message }]);
    }, []);

    addToastRef.current = addToast;

    const removeToast = useCallback((id: number) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    useEffect(() => {
        const removeSuccess = router.on('success', (event) => {
            const page = event.detail.page;
            const url = page.url;

            // Апгрейд показывает результат через свой UpgradeResult overlay
            if (url === '/' || url.startsWith('/?')) return;

            const flash = page.props?.flash as { success?: string; error?: string } | undefined;

            if (flash?.success) {
                addToastRef.current?.('success', flash.success);
            }
            if (flash?.error) {
                addToastRef.current?.('error', flash.error);
            }
        });

        const removeError = router.on('error', (event) => {
            const errors = event.detail.errors as Record<string, string>;
            if (errors && typeof errors === 'object') {
                Object.values(errors).forEach((msg) => {
                    if (typeof msg === 'string') {
                        addToastRef.current?.('error', msg);
                    }
                });
            }
        });

        const removeInvalid = router.on('invalid', (event) => {
            const status = event.detail.response?.status;
            if (status && status >= 500) {
                addToastRef.current?.('error', 'Произошла ошибка сервера. Попробуйте позже.');
            }
        });

        return () => {
            removeSuccess();
            removeError();
            removeInvalid();
        };
    }, []);

    return (
        <ToastContext.Provider value={{ toast: addToast }}>
            {children}
            <div className="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none max-w-[380px]">
                {toasts.map((t) => (
                    <ToastItem key={t.id} toast={t} onDismiss={removeToast} />
                ))}
            </div>
        </ToastContext.Provider>
    );
}

const ACCENT: Record<ToastType, { border: string; icon: string; bg: string }> = {
    success: {
        border: 'rgba(0, 191, 108, 0.3)',
        icon: '#00BF6C',
        bg: 'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(0, 191, 108, 0.08) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.85) 0%, rgba(7, 10, 16, 0.85) 100%)',
    },
    error: {
        border: 'rgba(234, 47, 47, 0.3)',
        icon: '#EA2F2F',
        bg: 'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(234, 47, 47, 0.08) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.85) 0%, rgba(7, 10, 16, 0.85) 100%)',
    },
    info: {
        border: 'rgba(78, 137, 255, 0.3)',
        icon: '#4E89FF',
        bg: 'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(78, 137, 255, 0.08) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.85) 0%, rgba(7, 10, 16, 0.85) 100%)',
    },
};

function ToastItem({ toast, onDismiss }: { toast: Toast; onDismiss: (id: number) => void }) {
    const [state, setState] = useState<'enter' | 'visible' | 'exit'>('enter');
    const accent = ACCENT[toast.type];

    useEffect(() => {
        requestAnimationFrame(() => setState('visible'));
        const timer = setTimeout(() => setState('exit'), 4000);
        return () => clearTimeout(timer);
    }, []);

    useEffect(() => {
        if (state === 'exit') {
            const timer = setTimeout(() => onDismiss(toast.id), 400);
            return () => clearTimeout(timer);
        }
    }, [state, toast.id, onDismiss]);

    return (
        <div
            onClick={() => setState('exit')}
            style={{
                border: `1px solid ${accent.border}`,
                background: accent.bg,
                boxShadow: '0 16px 48px 0 rgba(0, 0, 0, 0.40)',
            }}
            className={`pointer-events-auto flex items-start gap-3 p-4 rounded-[14px] backdrop-blur-[70px] cursor-pointer transition-all duration-400 ease-out ${
                state === 'enter'
                    ? 'opacity-0 translate-x-[40px] scale-95'
                    : state === 'visible'
                      ? 'opacity-100 translate-x-0 scale-100'
                      : 'opacity-0 translate-x-[20px] scale-95'
            }`}
        >
            <div className="flex-shrink-0 mt-0.5">
                {toast.type === 'success' && <SuccessIcon color={accent.icon} />}
                {toast.type === 'error' && <ErrorIcon color={accent.icon} />}
                {toast.type === 'info' && <InfoIcon color={accent.icon} />}
            </div>
            <div className="flex flex-col gap-0.5 min-w-0">
                <span className="text-white font-sf-display text-[13px] font-medium leading-[130%]">
                    {toast.type === 'success' ? 'Успешно' : toast.type === 'error' ? 'Ошибка' : 'Информация'}
                </span>
                <span className="text-white/50 font-sf-display text-[12px] leading-[140%] break-words">
                    {toast.message}
                </span>
            </div>
            <ProgressBar duration={4000} color={accent.icon} running={state === 'visible'} />
        </div>
    );
}

function ProgressBar({ duration, color, running }: { duration: number; color: string; running: boolean }) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!running || !ref.current) return;
        ref.current.style.transition = 'none';
        ref.current.style.width = '100%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (ref.current) {
                    ref.current.style.transition = `width ${duration}ms linear`;
                    ref.current.style.width = '0%';
                }
            });
        });
    }, [running, duration]);

    return (
        <div className="absolute bottom-0 left-4 right-4 h-[2px] rounded-full overflow-hidden">
            <div
                ref={ref}
                className="h-full rounded-full"
                style={{ background: color, opacity: 0.4, width: '100%' }}
            />
        </div>
    );
}

function SuccessIcon({ color }: { color: string }) {
    return (
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 1.5C4.86 1.5 1.5 4.86 1.5 9C1.5 13.14 4.86 16.5 9 16.5C13.14 16.5 16.5 13.14 16.5 9C16.5 4.86 13.14 1.5 9 1.5ZM7.5 12.75L3.75 9L4.8075 7.9425L7.5 10.6275L13.1925 4.935L14.25 6L7.5 12.75Z" fill={color} />
        </svg>
    );
}

function ErrorIcon({ color }: { color: string }) {
    return (
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 1.5C4.86 1.5 1.5 4.86 1.5 9C1.5 13.14 4.86 16.5 9 16.5C13.14 16.5 16.5 13.14 16.5 9C16.5 4.86 13.14 1.5 9 1.5ZM9.75 13.5H8.25V12H9.75V13.5ZM9.75 10.5H8.25V4.5H9.75V10.5Z" fill={color} />
        </svg>
    );
}

function InfoIcon({ color }: { color: string }) {
    return (
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 1.5C4.86 1.5 1.5 4.86 1.5 9C1.5 13.14 4.86 16.5 9 16.5C13.14 16.5 16.5 13.14 16.5 9C16.5 4.86 13.14 1.5 9 1.5ZM9.75 13.5H8.25V8.25H9.75V13.5ZM9.75 6.75H8.25V5.25H9.75V6.75Z" fill={color} />
        </svg>
    );
}
