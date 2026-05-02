import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { SoundIcon } from '@/Components/UI/Icons';
import { Chip, ChipLabel, DropdownItem } from '../primitives';
import { useClickOutside } from '../useClickOutside';

export default function SoundMenu() {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [enabled, setEnabled] = useState(true);
    const ref = useClickOutside<HTMLDivElement>(() => setOpen(false), open);

    return (
        <div ref={ref} className="relative">
            <Chip interactive onClick={() => setOpen((v) => !v)}>
                <SoundIcon />
                <ChipLabel>{enabled ? t('header.sound_on') : t('header.sound_off')}</ChipLabel>
            </Chip>
            {open && (
                <div className="animate-dropdown-in absolute top-[calc(100%+4px)] right-0 flex flex-col gap-[1px] bg-accent rounded-[12px] p-1 z-50 min-w-[112px] shadow-lg">
                    <DropdownItem
                        active={enabled}
                        onClick={() => {
                            setEnabled(true);
                            setOpen(false);
                        }}
                    >
                        {t('header.sound_on')}
                    </DropdownItem>
                    <DropdownItem
                        active={!enabled}
                        onClick={() => {
                            setEnabled(false);
                            setOpen(false);
                        }}
                    >
                        {t('header.sound_off')}
                    </DropdownItem>
                </div>
            )}
        </div>
    );
}
