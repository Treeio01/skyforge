export function ProfileStatCardsFallback() {
    return (
        <div className="flex flex-col 550:flex-row gap-3.5 550:min-h-[391px] w-full animate-pulse">
            {[1, 2, 3].map((i) => (
                <div key={i} className="flex-1 rounded-[14px] bg-white/5 min-h-[120px]" />
            ))}
        </div>
    );
}

export function ProfileTabsFallback() {
    return <div className="min-h-[320px] rounded-[14px] bg-white/5 animate-pulse" />;
}
