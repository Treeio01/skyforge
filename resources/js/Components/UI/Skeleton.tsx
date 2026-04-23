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
        <div className="flex flex-col gap-2 p-3 bg-surface-2 rounded-[16px] border border-border">
            <Skeleton className="w-full aspect-square" rounded="rounded-[12px]" />
            <Skeleton className="h-3 w-3/4" />
            <Skeleton className="h-3 w-1/2" />
            <Skeleton className="h-8 w-full mt-1" />
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
