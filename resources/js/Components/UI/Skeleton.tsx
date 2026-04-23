interface SkeletonProps {
    className?: string;
    rounded?: string;
}

export function Skeleton({ className = '', rounded = 'rounded-[10px]' }: SkeletonProps) {
    return (
        <div className={`animate-pulse bg-surface-2 ${rounded} ${className}`} />
    );
}

export function SkeletonSkinCard() {
    return (
        <div className="flex flex-col justify-between min-h-[126px] pt-3 px-2.5 pb-3.5 rounded-[14px] bg-white/4 overflow-hidden relative">
            <Skeleton className="h-[14px] w-14" rounded="rounded-[4px]" />
            <Skeleton className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[80px] h-[60px]" rounded="rounded-[6px]" />
            <div className="flex flex-col gap-1">
                <Skeleton className="h-[13px] w-2/3" rounded="rounded-[4px]" />
                <Skeleton className="h-[13px] w-1/2" rounded="rounded-[4px]" />
            </div>
        </div>
    );
}

export function SkeletonFeedItem() {
    return (
        <div className="flex items-center gap-3 p-2">
            <Skeleton className="w-8 h-8 shrink-0" rounded="rounded-full" />
            <div className="flex flex-col gap-1.5 flex-1">
                <Skeleton className="h-3 w-1/2" />
                <Skeleton className="h-3 w-1/3" />
            </div>
            <Skeleton className="w-10 h-10 shrink-0" rounded="rounded-[8px]" />
        </div>
    );
}

export function SkeletonDepositForm() {
    return (
        <div className="flex flex-col gap-4">
            <div className="flex gap-2">
                {[1, 2, 3, 4].map((i) => (
                    <Skeleton key={i} className="h-10 flex-1" rounded="rounded-[10px]" />
                ))}
            </div>
            <Skeleton className="h-12 w-full" />
            <Skeleton className="h-10 w-full" />
            <Skeleton className="h-12 w-full" />
        </div>
    );
}
