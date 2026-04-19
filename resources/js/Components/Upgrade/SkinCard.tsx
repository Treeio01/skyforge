import { SkinRarity } from './LiveFeedItem';

export type SkinEntry = {
    id: string | number;
    rarity: SkinRarity;
    weapon: string;
    name: string;
    price: string;
    priceKopecks: number;
    image: string;
    backendSkinId?: number;
    backendUserSkinId?: number;
};

export function ItemBackgroundLines() {
    return (
        <svg
            width="149"
            height="126"
            viewBox="0 0 149 126"
            fill="none"
            preserveAspectRatio="xMidYMid slice"
            xmlns="http://www.w3.org/2000/svg"
            className="absolute left-0 top-0 w-full h-full"
            style={{ color: 'var(--rarity-accent)' }}
        >
            <g opacity="0.2">
                <path
                    d="M89.4246 1.84918C90.4196 0.854208 24.8895 26.8617 -8 39.9899L96.0578 -3.54028L83.6206 12.2135L149.123 -3.54028L67.8668 22.5778C74.6382 16.0828 88.4297 2.84416 89.4246 1.84918Z"
                    fill="currentColor"
                    stroke="currentColor"
                    strokeWidth="0.829146"
                />
                <path
                    d="M64.9648 71.083L-4.26886 109.638V100.518L64.9648 71.083L13.1432 135.342L150.781 53.6709L61.6482 138.866L97.3015 132.854L113.47 137.829L77.8166 153.998C85.0025 149.852 99.5402 141.312 100.203 140.317C100.867 139.322 65.3794 147.917 47.5527 152.339L61.6482 138.866L150.781 53.6709L13.1432 135.342L64.9648 71.083Z"
                    fill="currentColor"
                />
                <path
                    d="M64.9648 71.083L-4.26886 109.638V100.518L64.9648 71.083ZM64.9648 71.083L13.1432 135.342L150.781 53.6709L61.6482 138.866M61.6482 138.866L47.5527 152.339C65.3794 147.917 100.867 139.322 100.204 140.317C99.5402 141.312 85.0025 149.852 77.8166 153.998L113.47 137.829L97.3015 132.854L61.6482 138.866Z"
                    stroke="currentColor"
                    strokeWidth="0.829146"
                />
                <path
                    d="M2.77887 71.4974C4.1055 71.8291 87.9045 40.1281 127.565 25.0652C124.94 27.1381 120.269 31.2009 122.59 30.8693C124.912 30.5376 146.497 19.9522 157 14.7009"
                    stroke="currentColor"
                    strokeWidth="1.65829"
                />
            </g>
        </svg>
    );
}

function SelectedBadge() {
    return (
        <div
            className="flex absolute right-0 top-0 w-[30px] h-[30px] items-center justify-center rounded-bl-[14px] z-50"
            style={{ backgroundColor: 'var(--rarity-accent)' }}
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="11"
                height="8"
                viewBox="0 0 11 8"
                fill="none"
            >
                <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M10.5531 0.237341C10.7049 0.389184 10.7902 0.595099 10.7902 0.809804C10.7902 1.02451 10.7049 1.23042 10.5531 1.38227L4.48412 7.45129C4.40392 7.53151 4.3087 7.59515 4.2039 7.63857C4.0991 7.68198 3.98677 7.70433 3.87333 7.70433C3.75989 7.70433 3.64757 7.68198 3.54277 7.63857C3.43797 7.59515 3.34275 7.53151 3.26254 7.45129L0.247194 4.43648C0.169859 4.36179 0.108174 4.27244 0.0657376 4.17365C0.0233016 4.07487 0.000964825 3.96862 3.05718e-05 3.8611C-0.000903681 3.75359 0.0195834 3.64697 0.0602962 3.54746C0.101009 3.44795 0.161132 3.35754 0.237158 3.28152C0.313183 3.20549 0.403589 3.14537 0.503099 3.10466C0.602609 3.06395 0.709231 3.04346 0.816743 3.04439C0.924255 3.04533 1.03051 3.06766 1.12929 3.1101C1.22808 3.15254 1.31743 3.21422 1.39212 3.29156L3.87306 5.7725L9.40768 0.237341C9.48287 0.162098 9.57216 0.102409 9.67043 0.0616849C9.7687 0.0209608 9.87404 0 9.98041 0C10.0868 0 10.1921 0.0209608 10.2904 0.0616849C10.3887 0.102409 10.478 0.162098 10.5531 0.237341Z"
                    fill="#D9D9D9"
                />
            </svg>
        </div>
    );
}

interface SkinCardProps extends SkinEntry {
    selected?: boolean;
    dimmed?: boolean;
    onClick?: () => void;
}

export default function SkinCard({
    rarity,
    image,
    weapon,
    name,
    price,
    selected = false,
    dimmed = false,
    onClick,
}: SkinCardProps) {
    return (
        <div
            onClick={onClick}
            className={`rarity-${rarity} animate-skin-card-in flex flex-col w-full min-h-[126px] gap-[59px] pt-3 px-2.5 pb-3.5 rounded-[14px] relative overflow-hidden bg-[#BED4FF]/2 bg-linear-to-b from-transparent to-[var(--rarity-from)] cursor-pointer transition-[opacity,filter,box-shadow] duration-200 ${
                dimmed ? 'opacity-50 blur-[4px]' : ''
            }`}
            style={{
                borderBottom: '2px solid var(--rarity-accent)',
                boxShadow: selected
                    ? 'inset 0 0 0 2px var(--rarity-accent)'
                    : 'none',
            }}
        >
            {selected && <SelectedBadge />}
            <ItemBackgroundLines />
            <img
                src={image}
                className="absolute left-1/2 top-1/2 -translate-1/2 w-full h-full max-w-[120px] max-h-[90px] object-contain z-10"
                alt=""
            />
            <span className="text-white font-sf-display text-sm leading-[104%] relative z-10">
                {price}
            </span>
            <div className="flex flex-col relative z-10 min-w-0">
                <span className="text-white font-sf-display text-[13px] font-medium leading-[104%] whitespace-nowrap">
                    {weapon}
                </span>
                <span
                    className="text-white font-gotham font-light text-[13px] leading-[104%] block truncate"
                    title={name}
                >
                    {name}
                </span>
            </div>
        </div>
    );
}
