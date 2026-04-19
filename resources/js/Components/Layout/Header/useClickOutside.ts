import { useEffect, useRef } from 'react';

export function useClickOutside<T extends HTMLElement>(
    onClose: () => void,
    active: boolean,
) {
    const ref = useRef<T>(null);
    useEffect(() => {
        if (!active) return;
        const handler = (e: MouseEvent) => {
            if (!ref.current?.contains(e.target as Node)) onClose();
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [active, onClose]);
    return ref;
}
