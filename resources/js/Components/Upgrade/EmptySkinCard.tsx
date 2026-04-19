interface EmptySkinCardProps {
    onClick?: () => void;
}

export default function EmptySkinCard({ onClick }: EmptySkinCardProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="relative flex w-full items-center justify-center rounded-[14px] cursor-pointer min-h-[126px] max-h-[126px]"
        >
            <svg
                className="absolute inset-0 w-full h-full pointer-events-none"
                preserveAspectRatio="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <rect
                    x="1"
                    y="1"
                    width="calc(100% - 2px)"
                    height="calc(100% - 2px)"
                    rx="13"
                    ry="13"
                    fill="none"
                    stroke="#0C0F15"
                    strokeWidth="2"
                    pathLength="32"
                    strokeDasharray="3 1"
                    strokeDashoffset="-1.5"
                />
            </svg>
            <div className="relative flex rounded-full p-[8.25px] bg-[#0C0F15]">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="17"
                    height="17"
                    viewBox="0 0 17 17"
                    fill="none"
                >
                    <path
                        d="M13.8059 8.25H2.69418M8.25002 2.69416V13.8058"
                        stroke="white"
                        strokeOpacity="0.32"
                        strokeWidth="0.785714"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </div>
        </button>
    );
}
