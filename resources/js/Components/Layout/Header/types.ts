import { ReactNode } from 'react';

export type LocaleCode = 'ru' | 'en';

export type Locale = {
    code: LocaleCode;
    label: string;
    currency: string;
};

export type ChipTone = 'light' | 'dark' | 'inherit';

export interface ChipProps {
    children: ReactNode;
    className?: string;
    interactive?: boolean;
    onClick?: () => void;
}

export interface ChipLabelProps {
    children: ReactNode;
    tone?: ChipTone;
    className?: string;
}

export interface DividerProps {
    className?: string;
}

export interface DropdownItemProps {
    children: ReactNode;
    active?: boolean;
    onClick: () => void;
}
