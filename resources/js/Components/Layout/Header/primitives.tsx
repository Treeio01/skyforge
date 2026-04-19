import {
    ChipLabelProps,
    ChipProps,
    DividerProps,
    DropdownItemProps,
} from './types';

export function Chip({
    children,
    className = 'bg-accent',
    interactive = false,
    onClick,
}: ChipProps) {
    const hover = interactive
        ? 'cursor-pointer transition-[filter,background-color,color] duration-150 hover:brightness-125'
        : '';
    return (
        <div
            onClick={onClick}
            className={`flex md:p-3 p-2 rounded-[12px] gap-1 items-center ${className} ${hover}`}
        >
            {children}
        </div>
    );
}

export function ChipLabel({
    children,
    tone = 'light',
    className = '',
}: ChipLabelProps) {
    const color =
        tone === 'dark'
            ? 'text-[#23262C]'
            : tone === 'light'
              ? 'text-white'
              : '';
    return (
        <span
            className={`${color} text-[10px] md:text-[13px] leading-[120%] font-sf-display ${className}`}
        >
            {children}
        </span>
    );
}

export function Divider({ className = '' }: DividerProps) {
    return (
        <div className={`flex min-h-[24px] w-[1px] bg-black/7 ${className}`} />
    );
}

export function DropdownItem({
    children,
    active = false,
    onClick,
}: DropdownItemProps) {
    return (
        <button
            onClick={onClick}
            className={`flex px-3 py-2 rounded-[8px] items-center gap-1 cursor-pointer transition-colors ${
                active ? 'bg-white/5' : 'hover:bg-white/5'
            }`}
        >
            <span className="text-white text-[13px] leading-[120%]">
                {children}
            </span>
        </button>
    );
}
