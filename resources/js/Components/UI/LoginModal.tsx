import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function LoginModal() {
    const isGuest = !usePage<PageProps>().props.auth.user;
    const [visible, setVisible] = useState(false);
    const [adultChecked, setAdultChecked] = useState(false);
    const [termsChecked, setTermsChecked] = useState(false);
    const canLogin = adultChecked && termsChecked;

    useEffect(() => {
        if (!isGuest) return;
        const show = () => setVisible(true);
        window.addEventListener('show-login-modal', show);
        return () => window.removeEventListener('show-login-modal', show);
    }, [isGuest]);

    if (!isGuest) return null;

    return (
        <div
            className={`flex z-[1000] justify-center items-center bg-black/40 fixed inset-0 transition-opacity duration-500 ease-out ${
                visible ? 'opacity-100' : 'opacity-0 pointer-events-none'
            }`}
            onClick={() => setVisible(false)}
        >
            <div
                onClick={(e) => e.stopPropagation()}
                style={{
                    border: '1px solid rgba(255, 255, 255, 0.21)',
                    background:
                        'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)',
                    boxShadow: '0 26px 80px 0 rgba(0, 0, 0, 0.30)',
                }}
                className={`flex rounded-[20px] backdrop-blur-[70px] w-full max-w-[490px] p-[25px] gap-[25px] flex-col transition-all duration-500 ease-out ${
                    visible
                        ? 'opacity-100 scale-100 translate-y-0'
                        : 'opacity-0 scale-95 translate-y-4'
                }`}
            >
                <div className="flex w-full">
                    <div className="flex w-full flex-col gap-[3px]">
                        <span className="text-white font-gotham font-medium text-2xl leading-[100%]">
                            Добро пожаловать!
                        </span>
                        <p className="font-inter text-sm leading-[100%] text-white/40">
                            Войдите через Steam чтобы начать
                        </p>
                    </div>
                    <button onClick={() => setVisible(false)} className="cursor-pointer p-1">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                            <path d="M7.5 2.5L2.5 7.5M2.5 2.5L7.5 7.5" stroke="white" strokeOpacity="0.32" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                    </button>
                </div>

                <div className="flex flex-col gap-2.5">
                    <label htmlFor="login-adult" className="text-white font-inter text-[12px] flex gap-[5px] items-center cursor-pointer">
                        <input id="login-adult" type="checkbox" checked={adultChecked} onChange={(e) => setAdultChecked(e.target.checked)} />
                        Я подтверждаю, что мне больше 18 лет
                    </label>
                    <label htmlFor="login-terms" className="text-white font-inter text-[12px] flex gap-[5px] items-center cursor-pointer">
                        <input id="login-terms" type="checkbox" checked={termsChecked} onChange={(e) => setTermsChecked(e.target.checked)} />
                        Я принимаю правила и условия использования сайта
                    </label>
                </div>

                <a
                    href={canLogin ? '/auth/steam' : undefined}
                    onClick={(e) => { if (!canLogin) e.preventDefault(); }}
                    style={{
                        background: 'radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)',
                    }}
                    className={`py-[18px] px-[14px] flex rounded-[12px] justify-center items-center gap-[5px] transition-all duration-200 ${
                        canLogin
                            ? 'hover:brightness-125 hover:shadow-[0_0_20px_rgba(30,60,120,0.6)] active:scale-[0.98] cursor-pointer'
                            : 'opacity-40 cursor-not-allowed'
                    }`}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M6.6809 5.49624L5.18115 7.6735C4.82761 7.65744 4.4717 7.75746 4.17626 7.95162L0.883014 6.59677C0.883014 6.59677 0.80681 7.84964 1.12438 8.78335L3.45253 9.7434C3.56943 10.2655 3.92779 10.7234 4.45652 10.9437C5.32154 11.3049 6.31896 10.8932 6.6788 10.0283C6.77247 9.80224 6.81613 9.56516 6.80979 9.32856L9.00831 7.79705C10.2925 7.79705 11.3362 6.75081 11.3362 5.46596C11.3362 4.18104 10.2925 3.13574 9.00831 3.13574C7.76796 3.13574 6.61138 4.21802 6.6809 5.49624ZM6.32053 9.87798C6.04202 10.5462 5.27364 10.8632 4.60571 10.5851C4.29758 10.4568 4.06495 10.2218 3.93074 9.94159L4.68857 10.2555C5.18115 10.4605 5.74627 10.2271 5.95102 9.73496C6.15642 9.24233 5.92341 8.67664 5.43109 8.47159L4.64771 8.14718C4.94997 8.0326 5.29363 8.0284 5.61471 8.16193C5.93837 8.2965 6.18954 8.54994 6.32263 8.87378C6.45576 9.19766 6.45524 9.55514 6.32053 9.87798ZM9.00831 7.01896C8.15344 7.01896 7.45742 6.32233 7.45742 5.46596C7.45742 4.6103 8.15344 3.91349 9.00831 3.91349C9.86376 3.91349 10.5597 4.6103 10.5597 5.46596C10.5597 6.32233 9.86376 7.01896 9.00831 7.01896ZM7.84618 5.4636C7.84618 4.81943 8.36807 4.29692 9.01094 4.29692C9.65437 4.29692 10.1762 4.81943 10.1762 5.4636C10.1762 6.10786 9.65437 6.62989 9.01094 6.62989C8.36807 6.62989 7.84618 6.10786 7.84618 5.4636Z" fill="white" />
                    </svg>
                    <span className="text-sm text-white font-sf-display font-medium leading-[120%]">
                        Войти через Steam
                    </span>
                </a>
            </div>
        </div>
    );
}
