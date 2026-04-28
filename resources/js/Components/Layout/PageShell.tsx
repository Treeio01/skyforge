import { ReactNode } from 'react';
import { PageTitleIcon } from '@/Components/UI/Icons';

interface PageShellProps {
    icon?: ReactNode;
    title: string;
    subtitle?: string;
    toolbar?: ReactNode;
    children: ReactNode;
    contentClassName?: string;
}

export default function PageShell({
    icon,
    title,
    subtitle,
    toolbar,
    children,
    contentClassName = '',
}: PageShellProps) {
    return (
        <div className="pt-[6px] px-[6px] pb-[6px] flex flex-col w-full">
            <div className="flex flex-col w-full bg-[#070A10] rounded-[24px] overflow-hidden min-h-[calc(100svh-72px)]">
                <div className="flex flex-col 1024:flex-row 1024:items-end 1024:justify-between gap-3 px-4 1024:px-8 pt-5 1024:pt-8">
                    <div className="flex flex-col gap-1 min-w-0">
                        <div className="flex items-center gap-1.5">
                            {icon ?? <PageTitleIcon />}
                            <h1 className="text-white text-2xl 1024:text-[27px] leading-[104%] font-gotham font-medium truncate">
                                {title}
                            </h1>
                        </div>
                        {subtitle && (
                            <span className="text-[#9C9DA9] font-sf-display text-[12px] 1024:text-base leading-[120%]">
                                {subtitle}
                            </span>
                        )}
                    </div>
                    {toolbar && <div className="flex flex-wrap items-center gap-2">{toolbar}</div>}
                </div>
                <div className={`flex flex-col flex-1 min-h-0 px-4 1024:px-8 py-5 1024:py-8 ${contentClassName}`}>
                    {children}
                </div>
            </div>
        </div>
    );
}
