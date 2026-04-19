import React, { ReactNode } from 'react';

interface SkinsPanelProps {
    icon: ReactNode;
    title: string;
    toolbar?: ReactNode;
    children: ReactNode;
    onScrollEnd?: () => void;
}

export default function SkinsPanel({
    icon,
    title,
    toolbar,
    children,
    onScrollEnd,
}: SkinsPanelProps) {
    const handleScroll = (e: React.UIEvent<HTMLDivElement>) => {
        const el = e.currentTarget;
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 100) {
            onScrollEnd?.();
        }
    };

    return (
        <div className="flex flex-col overflow-hidden rounded-t-[14px] w-full max-w-[630px] bg-accent/90">
            <div
                className={`flex bg-accent justify-between items-center ${
                    toolbar ? 'py-[6.5px] px-3.5' : 'p-3.5'
                }`}
            >
                <div className="flex items-center gap-[5px]">
                    {icon}
                    <span className="text-white font-sf-display text-[13px] leading-[104%]">
                        {title}
                    </span>
                </div>
                {toolbar}
            </div>
            <div
                className="skins-scroll h-full grid p-2.5 gap-[4px] bg-accent/90 max-h-[361px] overflow-y-auto justify-center grid-cols-[repeat(auto-fit,145px)]"
                onScroll={handleScroll}
            >
                {children}
            </div>
        </div>
    );
}
