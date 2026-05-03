import { useEffect, useRef, useState } from 'react';

interface VideoPreloaderProps {
    srcs: string[];
    preload?: 'auto' | 'metadata' | 'none';
    onAllLoaded?: () => void;
}

export default function VideoPreloader({
    srcs,
    preload = 'auto',
    onAllLoaded,
}: VideoPreloaderProps) {
    const [loadedCount, setLoadedCount] = useState(0);
    const firedRef = useRef(false);
    const total = srcs.length;

    useEffect(() => {
        if (total === 0) {
            onAllLoaded?.();
        }
    }, [total, onAllLoaded]);

    useEffect(() => {
        if (! firedRef.current && total > 0 && loadedCount >= total) {
            firedRef.current = true;
            onAllLoaded?.();
        }
    }, [loadedCount, total, onAllLoaded]);

    const markLoaded = () => setLoadedCount((c) => c + 1);

    if (total === 0) {
        return null;
    }

    return (
        <div aria-hidden className="hidden">
            {srcs.map((src) => (
                <video
                    key={src}
                    src={src}
                    preload={preload === 'none' ? 'none' : preload}
                    muted
                    playsInline
                    onCanPlayThrough={markLoaded}
                    onError={markLoaded}
                />
            ))}
        </div>
    );
}
