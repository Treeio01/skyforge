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
        <div className="flex flex-col overflow-hidden min-h-[361px] max-h-[500px] rounded-t-[14px] w-full max-w-[630px] bg-accent/90 relative">
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
                className="skins-scroll flex-1 min-h-0 grid content-start auto-rows-min p-2.5 pb-[120px] gap-[4px] bg-accent/90 overflow-y-auto grid-cols-[repeat(auto-fill,minmax(135px,1fr))]"
                onScroll={handleScroll}
            >
                {children}
            </div>
            <div className="pointer-events-none absolute left-0 right-0 bottom-0 h-[140px] z-30 bg-linear-to-t from-[#151B27] from-30% via-[#151B27]/60 via-65% to-transparent" />
        </div>
    );
}
