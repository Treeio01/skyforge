import { SkinEntry } from './SkinCard';
import SkinCardPreview from './SkinCardPreview';

export type UpgradeResultVariant = 'win' | 'lose';

interface UpgradeResultProps {
    variant: UpgradeResultVariant;
    skin: SkinEntry;
    description?: string;
    className?: string;
}

type VariantStyles = {
    title: string;
    titleColor: string;
    accentStripe: string;
    tagBg: string;
    tagColor: string;
    skinNameColor: string;
    descriptionColor: string;
    bgImage: string;
    gradientRgb: string;
};

const VARIANTS: Record<UpgradeResultVariant, VariantStyles> = {
    win: {
        title: 'ПОБЕДА',
        titleColor: '#97C9FA',
        accentStripe: '#97C9FA',
        tagBg: '#97C9FA',
        tagColor: '#11171D',
        skinNameColor: '#B5F1FF',
        descriptionColor: '#A0D6FF',
        bgImage: '/assets/img/win.png',
        gradientRgb: '51, 59, 70', // #333B46
    },
    lose: {
        title: 'ПРОИГРЫШ',
        titleColor: '#DB4538',
        accentStripe: '#DB4538',
        tagBg: '#EABF55',
        tagColor: '#11171D',
        skinNameColor: '#FFDE65',
        descriptionColor: '#FFE68A',
        bgImage: '/assets/img/lose.png',
        gradientRgb: '70, 51, 51', // #463333
    },
};

const DEFAULT_DESCRIPTION: Record<UpgradeResultVariant, string> = {
    win: 'Поздравляем! Ваш апгрейд удался!',
    lose: 'Ничего, в другой раз повезет! Наверное...',
};

export default function UpgradeResult({
    variant,
    skin,
    description,
    className = '',
}: UpgradeResultProps) {
    const v = VARIANTS[variant];
    const text = description ?? DEFAULT_DESCRIPTION[variant];

    return (
        <div
            className={`absolute left-1/2 top-[18px] -translate-x-1/2 z-[200] w-full max-w-[757px] ${className}`}
        >
            <div className="animate-upgrade-result-in flex flex-col items-center gap-2">
                <div className="flex rounded-[14px] overflow-hidden items-stretch animate-upgrade-result-title">
                    <div
                        className="flex w-[7px]"
                        style={{ backgroundColor: v.accentStripe }}
                    />
                    <div
                        className="flex py-2 md:py-3 px-[73px] md:px-[85px]"
                        style={{
                            backgroundImage: `linear-gradient(90deg, rgba(${v.gradientRgb}, 1) 0%, rgba(${v.gradientRgb}, 0.66) 50%, rgba(${v.gradientRgb}, 1) 100%), url("/assets/img/result-block-img-pattern.png")`,
                            backgroundRepeat: 'no-repeat, repeat',
                            backgroundSize: 'auto, 4px 4px',
                        }}
                    >
                        <div className="flex flex-col items-center">
                            <h1
                                className="font-bold font-montserrat text-[39px] md:text-[42px] leading-[100%] text-center"
                                style={{ color: v.titleColor }}
                            >
                                {v.title}
                            </h1>
                            <span className="font-medium text-[13px] leading-[100%] text-white/29 font-sf-display">
                                This's random text
                            </span>
                        </div>
                    </div>
                    <div
                        className="flex w-[7px]"
                        style={{ backgroundColor: v.accentStripe }}
                    />
                </div>

                <div
                    className="animate-upgrade-result-skin flex w-full px-[56px] py-[5px] md:py-[8px] md:px-[110px] gap-2 justify-center"
                    style={{ background: `url(${v.bgImage}) center / contain no-repeat` }}
                >
                    <SkinCardPreview {...skin} />
                    <div className="flex flex-col gap-[10px] md:gap-[19px] flex-1 py-[7px]">
                        <div className="flex flex-col gap-0.5 md:gap-1">
                            <div
                                className="flex px-1 md:px-2 py-[1px] md:py-[3px] w-max"
                                style={{ backgroundColor: v.tagBg }}
                            >
                                <span
                                    className="font-montserrat font-bold text-[7px] md:text-[15px] leading-[100%]"
                                    style={{ color: v.tagColor }}
                                >
                                    {skin.weapon}
                                </span>
                            </div>
                            <span
                                className="font-montserrat font-medium text-[10px] md:text-[21px] leading-[100%]"
                                style={{
                                    color: v.skinNameColor,
                                    textShadow: '-2px 2px 6px rgba(0, 0, 0, 0.40)',
                                }}
                            >
                                {skin.name}
                            </span>
                        </div>
                        <span
                            className="text-[6px] md:text-[12px] font-medium font-montserrat leading-[100%]"
                            style={{
                                color: v.descriptionColor,
                                textShadow: '-2px 2px 6px rgba(0, 0, 0, 0.40)',
                            }}
                        >
                            {text}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
