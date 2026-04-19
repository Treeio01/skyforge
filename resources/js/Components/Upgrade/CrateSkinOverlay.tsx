import { CSSProperties } from 'react';

interface CrateSkinOverlayProps {
    price: string;
    weapon: string;
    name: string;
    image: string;
    style?: CSSProperties;
    className?: string;
    onRemove?: () => void;
}

function RemoveIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="11"
            height="11"
            viewBox="0 0 11 11"
            fill="none"
        >
            <path
                d="M7.89468 7.40865L2.80133 2.74146M7.56251 2.65835L3.1335 7.49176"
                stroke="white"
                strokeOpacity="0.36"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

export default function CrateSkinOverlay({
    price,
    weapon,
    name,
    image,
    style,
    className = '',
    onRemove,
}: CrateSkinOverlayProps) {
    return (
        <div
            className={`absolute z-[60] flex flex-col justify-between ${className}`}
            style={style}
        >
            <img
                src={image}
                className="max-w-[204px] w-full max-h-[153px] h-full absolute left-1/2 top-1/2 -translate-1/2 object-contain"
                alt=""
            />
            <div className="flex w-full items-center justify-between relative z-10">
                <span className="font-gotham text-[1.1cqw] leading-[104%] font-light text-white/50 whitespace-nowrap">
                    {price}
                </span>
                <button
                    type="button"
                    onClick={onRemove}
                    className="p-[5px] rounded-[4px] border border-white/6 bg-white/6 cursor-pointer transition-colors duration-150 hover:bg-white/10"
                >
                    <RemoveIcon />
                </button>
            </div>
            <div className="flex flex-col relative z-10">
                <span className="text-white/37 font-gotham text-[1.2cqw] font-medium leading-[104%] whitespace-nowrap">
                    {weapon}
                </span>
                <span className="text-white font-gotham text-[1.2cqw] leading-[104%] font-medium whitespace-nowrap truncate">
                    {name}
                </span>
            </div>
        </div>
    );
}
