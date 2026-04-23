import { CSSProperties, ReactNode } from 'react';

type ButtonVariant = 'primary' | 'ghost' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps {
    children: ReactNode;
    onClick?: () => void;
    disabled?: boolean;
    loading?: boolean;
    type?: 'button' | 'submit';
    className?: string;
    style?: CSSProperties;
    variant?: ButtonVariant;
    size?: ButtonSize;
}

const VARIANTS: Record<ButtonVariant, { bg: string; shadow: string; hoverShadow: string }> = {
    primary: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)',
        shadow: '0 0 0 0 #0E1E39',
        hoverShadow: '0 0 20px rgba(30,60,120,0.6)',
    },
    ghost: {
        bg: 'transparent',
        shadow: 'none',
        hoverShadow: 'none',
    },
    danger: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #511212 0%, #370909 100%)',
        shadow: '0 0 0 0 #391010',
        hoverShadow: '0 0 20px rgba(120,30,30,0.6)',
    },
};

const SIZES: Record<ButtonSize, string> = {
    sm: 'py-2 px-3 text-xs rounded-[10px]',
    md: 'py-3 px-[14px] text-sm rounded-[12px]',
    lg: 'py-4 px-5 text-base rounded-[14px]',
};

function Spinner() {
    return (
        <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    );
}

export default function Button({
    children,
    onClick,
    disabled = false,
    loading = false,
    type = 'button',
    className = '',
    style,
    variant = 'primary',
    size = 'md',
}: ButtonProps) {
    const v = VARIANTS[variant];
    const isDisabled = disabled || loading;

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={isDisabled}
            style={{
                background: v.bg,
                boxShadow: v.shadow,
                ...style,
            }}
            onMouseEnter={(e) => {
                if (!isDisabled && v.hoverShadow !== 'none') {
                    (e.currentTarget as HTMLButtonElement).style.boxShadow = v.hoverShadow;
                }
            }}
            onMouseLeave={(e) => {
                (e.currentTarget as HTMLButtonElement).style.boxShadow = v.shadow;
            }}
            className={`flex justify-center items-center gap-[5px] transition-all duration-200 hover:brightness-125 active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${SIZES[size]} ${className}`}
        >
            {loading ? <Spinner /> : children}
        </button>
    );
}
