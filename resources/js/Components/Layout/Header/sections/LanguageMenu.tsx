import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { LOCALES } from '../constants';
import { FlagRuIcon } from '@/Components/UI/Icons';
import { Chip, ChipLabel, DropdownItem } from '../primitives';
import { Locale } from '../types';
import { useClickOutside } from '../useClickOutside';

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

    return (
        <div ref={ref} className="relative">
            <Chip interactive onClick={() => setOpen((v) => !v)}>
                <FlagRuIcon />
                <ChipLabel>{locale.label}</ChipLabel>
                <ChipLabel>|</ChipLabel>
                <ChipLabel>{locale.currency}</ChipLabel>
            </Chip>
            {open && (
                <div className="animate-dropdown-in absolute top-[calc(100%+4px)] right-0 flex flex-col gap-[1px] bg-accent rounded-[12px] p-1 z-[500] min-w-[112px]">
                    {LOCALES.map((l) => (
                        <DropdownItem
                            key={l.code}
                            active={l.code === locale.code}
                            onClick={() => handleSelect(l)}
                        >
                            {`${l.label} | ${l.currency}`}
                        </DropdownItem>
                    ))}
                </div>
            )}
        </div>
    );
}
