import { useTranslation } from 'react-i18next';
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

export default function UpgradeResult({
    variant,
    skin,
    description,
    className = '',
}: UpgradeResultProps) {
    const { t } = useTranslation();
    const v = VARIANTS[variant];
    const title = variant === 'win' ? t('upgrade.result_win_label') : t('upgrade.result_lose_label');
    const text = description ?? (variant === 'win'
        ? t('upgrade.result_win_description')
        : t('upgrade.result_lose_description'));

    return (
        <div className={`relative w-full h-full ${className}`}>
        <div
            className="absolute left-1/2 top-[18px] -translate-x-1/2 z-[200] w-full max-w-[757px]"
        >
            <div className="animate-upgrade-result-in flex flex-col items-center gap-2">
                <div className="flex rounded-[14px] overflow-hidden items-stretch animate-upgrade-result-title">
                    <div
                        className="flex w-[7px]"
                        style={{ backgroundColor: v.accentStripe }}
                    />
                    <div
                        className="flex py-2 1024:py-3 px-[73px] 1024:px-[85px]"
                        style={{
                            backgroundImage: `linear-gradient(90deg, rgba(${v.gradientRgb}, 1) 0%, rgba(${v.gradientRgb}, 0.66) 50%, rgba(${v.gradientRgb}, 1) 100%), url("/assets/img/result-block-img-pattern.png")`,
                            backgroundRepeat: 'no-repeat, repeat',
                            backgroundSize: 'auto, 4px 4px',
                        }}
                    >
                        <div className="flex flex-col items-center">
                            <h1
                                className="font-bold font-montserrat text-[39px] 1024:text-[42px] leading-[100%] text-center"
                                style={{ color: v.titleColor }}
                            >
                                {title}
                            </h1>
                        </div>
                    </div>
                    <div
                        className="flex w-[7px]"
                        style={{ backgroundColor: v.accentStripe }}
                    />
                </div>

                <div
                    className="animate-upgrade-result-skin flex w-full px-[56px] py-[5px] 1024:py-[8px] 1024:px-[110px] gap-2 justify-center"
                    style={{ background: `url(${v.bgImage}) center / contain no-repeat` }}
                >
                    <SkinCardPreview {...skin} />
                    <div className="flex flex-col gap-[10px] 1024:gap-[19px] flex-1 py-[7px]">
                        <div className="flex flex-col gap-0.5 1024:gap-1">
                            <div
                                className="flex px-1 1024:px-2 py-[1px] 1024:py-[3px] w-max"
                                style={{ backgroundColor: v.tagBg }}
                            >
                                <span
                                    className="font-montserrat font-bold text-[7px] 1024:text-[15px] leading-[100%]"
                                    style={{ color: v.tagColor }}
                                >
                                    {skin.weapon}
                                </span>
                            </div>
                            <span
                                className="font-montserrat font-medium text-[10px] 1024:text-[21px] leading-[100%]"
                                style={{
                                    color: v.skinNameColor,
                                    textShadow: '-2px 2px 6px rgba(0, 0, 0, 0.40)',
                                }}
                            >
                                {skin.name}
                            </span>
                        </div>
                        <span
                            className="text-[6px] 1024:text-[12px] font-medium font-montserrat leading-[100%]"
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
        </div>
    );
}
