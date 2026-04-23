import { ReactNode } from 'react';

interface BadgeProps {
    rarity: string | null;
    className?: string;
    children?: ReactNode;
}

const RARITY_LABELS: Record<string, string> = {
    '#b0c3d9': 'Consumer',
    '#5e98d9': 'Industrial',
    '#4b69ff': 'Mil-Spec',
    '#8847ff': 'Restricted',
    '#d32ce6': 'Classified',
    '#eb4b4b': 'Covert',
    '#e4ae39': 'Contraband',
};

export default function Badge({ rarity, className = '', children }: BadgeProps) {
    const color = rarity ?? '#b0c3d9';
    const label = children ?? RARITY_LABELS[color.toLowerCase()] ?? 'Unknown';

    return (
        <span
            className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium ${className}`}
            style={{ backgroundColor: `${color}22`, color, border: `1px solid ${color}44` }}
        >
            {label}
        </span>
    );
}
