import { useState } from 'react';
import { SoundIcon } from '../icons';
import { Chip, ChipLabel, DropdownItem } from '../primitives';
import { useClickOutside } from '../useClickOutside';

export default function SoundMenu() {
    const [open, setOpen] = useState(false);
    const [enabled, setEnabled] = useState(true);
    const ref = useClickOutside<HTMLDivElement>(() => setOpen(false), open);

    return (
        <div ref={ref} className="relative">
            <Chip interactive onClick={() => setOpen((v) => !v)}>
                <SoundIcon />
                <ChipLabel>{enabled ? 'Вкл' : 'Выкл'}</ChipLabel>
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
                        Вкл
                    </DropdownItem>
                    <DropdownItem
                        active={!enabled}
                        onClick={() => {
                            setEnabled(false);
                            setOpen(false);
                        }}
                    >
                        Выкл
                    </DropdownItem>
                </div>
            )}
        </div>
    );
}
