import { useState } from 'react';
import { LOCALES } from '../constants';
import { FlagRuIcon } from '@/Components/UI/Icons';
import { Chip, ChipLabel, DropdownItem } from '../primitives';
import { Locale } from '../types';
import { useClickOutside } from '../useClickOutside';

export default function LanguageMenu() {
    const [open, setOpen] = useState(false);
    const [locale, setLocale] = useState<Locale>(LOCALES[0]);
    const ref = useClickOutside<HTMLDivElement>(() => setOpen(false), open);

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
                            onClick={() => {
                                setLocale(l);
                                setOpen(false);
                            }}
                        >
                            {`${l.label} | ${l.currency}`}
                        </DropdownItem>
                    ))}
                </div>
            )}
        </div>
    );
}
