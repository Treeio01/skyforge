import { useEffect, useRef, useState } from 'react';

interface VideoPreloaderProps {
    srcs: string[];
    onAllLoaded?: () => void;
}

export default function VideoPreloader({ srcs, onAllLoaded }: VideoPreloaderProps) {
    const [loadedCount, setLoadedCount] = useState(0);
    const firedRef = useRef(false);
    const total = srcs.length;

    useEffect(() => {
        if (!firedRef.current && total > 0 && loadedCount >= total) {
            firedRef.current = true;
            onAllLoaded?.();
        }
    }, [loadedCount, total, onAllLoaded]);

    const markLoaded = () => setLoadedCount((c) => c + 1);

    return (
        <div aria-hidden className="hidden">
            {srcs.map((src) => (
                <video
                    key={src}
                    src={src}
                    preload="auto"
                    muted
                    playsInline
                    onCanPlayThrough={markLoaded}
                    onError={markLoaded}
                />
            ))}
        </div>
    );
}
