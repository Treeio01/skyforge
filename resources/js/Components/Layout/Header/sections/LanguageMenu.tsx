import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { LOCALES } from '../constants';
import { FlagRuIcon, FlagEnIcon } from '@/Components/UI/Icons';
import { Chip, ChipLabel, DropdownItem } from '../primitives';
import { Locale, LocaleCode } from '../types';
import { useClickOutside } from '../useClickOutside';

const FLAGS: Record<LocaleCode, React.FC> = {
    ru: FlagRuIcon,
    en: FlagEnIcon,
};

export default function LanguageMenu() {
    const { i18n } = useTranslation();
    const [open, setOpen] = useState(false);
    const initial = LOCALES.find((l) => l.code === i18n.language) ?? LOCALES[0];
    const [locale, setLocale] = useState<Locale>(initial);
    const ref = useClickOutside<HTMLDivElement>(() => setOpen(false), open);

    useEffect(() => {
        const next = LOCALES.find((l) => l.code === i18n.language);
        if (next && next.code !== locale.code) {
            setLocale(next);
        }
    }, [i18n.language, locale.code]);

    const handleSelect = (l: Locale) => {
        setLocale(l);
        setOpen(false);
        i18n.changeLanguage(l.code);
    };

    const Flag = FLAGS[locale.code] ?? FlagRuIcon;

    return (
        <div ref={ref} className="relative">
            <Chip interactive onClick={() => setOpen((v) => !v)}>
                <Flag />
                <ChipLabel>{locale.label}</ChipLabel>
                <ChipLabel>|</ChipLabel>
                <ChipLabel>{locale.currency}</ChipLabel>
            </Chip>
            {open && (
                <div className="animate-dropdown-in absolute top-[calc(100%+4px)] right-0 flex flex-col gap-[1px] bg-accent rounded-[12px] p-1 z-[500] min-w-[112px]">
                    {LOCALES.map((l) => {
                        const ItemFlag = FLAGS[l.code] ?? FlagRuIcon;
                        return (
                            <DropdownItem
                                key={l.code}
                                active={l.code === locale.code}
                                onClick={() => handleSelect(l)}
                            >
                                <span className="flex items-center gap-[6px]">
                                    <ItemFlag />
                                    {`${l.label} | ${l.currency}`}
                                </span>
                            </DropdownItem>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
