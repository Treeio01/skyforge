import { ItemBackgroundLines, SkinEntry } from './SkinCard';

export default function SkinCardPreview({
    rarity,
    image,
    weapon,
    name,
    price,
}: SkinEntry) {
    return (
        <div
            className={`rarity-${rarity} flex flex-col w-[62px] h-[52px] pt-1.5 px-1.5 pb-1.5 relative 1024:w-[148px] 1024:h-[124px] 1024:pt-4 1024:px-4 1024:pb-4`}
            style={{
                background:
                    'linear-gradient(180deg, transparent 0%, color-mix(in srgb, var(--rarity-from) 74%, transparent) 28%, color-mix(in srgb, var(--rarity-from) 85%, transparent) 79%, transparent 98%)',
            }}
        >
            <ItemBackgroundLines />
            <img
                src={image}
                className="absolute left-1/2 top-1/2 -translate-1/2 w-[42px] h-[30px] object-contain z-10 1024:w-[100px] 1024:h-[72px]"
                alt=""
            />
            <span className="text-white font-sf-display text-[6px] leading-[104%] relative z-10 1024:text-[13px]">
                {price}
            </span>
            <div className="flex flex-col relative z-10 min-w-0 mt-auto">
                <span className="text-white font-sf-display text-[5px] font-medium leading-[104%] 1024:text-[11px]">
                    {weapon}
                </span>
                <span
                    className="text-white font-gotham font-light text-[5px] leading-[104%] block truncate 1024:text-[11px]"
                    title={name}
                >
                    {name}
                </span>
            </div>
        </div>
    );
}
