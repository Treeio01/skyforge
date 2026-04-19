import { useEffect } from 'react';

interface VideoPreloaderProps {
    srcs: string[];
}

export default function VideoPreloader({ srcs }: VideoPreloaderProps) {
    useEffect(() => {
        const nodes = document.querySelectorAll<HTMLVideoElement>(
            '[data-upgrade-preload]',
        );
        nodes.forEach((v) => v.load());
    }, []);

    return (
        <div aria-hidden className="hidden">
            {srcs.map((src) => (
                <video
                    key={src}
                    data-upgrade-preload
                    src={src}
                    preload="auto"
                    muted
                    playsInline
                />
            ))}
        </div>
    );
}
