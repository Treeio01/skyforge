/** @deprecated Use Button from @/Components/UI/Button instead. This file will be deleted after full migration. */
import { ReactNode } from 'react';

interface GradientButtonProps {
    children: ReactNode;
    onClick?: () => void;
    disabled?: boolean;
    type?: 'button' | 'submit';
    className?: string;
    variant?: 'blue' | 'red' | 'green';
}

const VARIANTS = {
    blue: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)',
        shadow: '0 0 0 0 #0E1E39',
        hoverShadow: '0_0_20px_rgba(30,60,120,0.6)',
    },
    red: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #511212 0%, #370909 100%)',
        shadow: '0 0 0 0 #391010',
        hoverShadow: '0_0_20px_rgba(120,30,30,0.6)',
    },
    green: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #124A21 0%, #092E13 100%)',
        shadow: '0 0 0 0 #0E3916',
        hoverShadow: '0_0_20px_rgba(30,120,60,0.6)',
    },
};

export default function GradientButton({
    children,
    onClick,
    disabled = false,
    type = 'button',
    className = '',
    variant = 'blue',
}: GradientButtonProps) {
    const v = VARIANTS[variant];

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={disabled}
            style={{ background: v.bg, boxShadow: v.shadow }}
            className={`py-3 px-[14px] flex rounded-[12px] justify-center items-center gap-[5px] transition-all duration-200 hover:brightness-125 hover:shadow-[${v.hoverShadow}] active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${className}`}
        >
            {children}
        </button>
    );
}
