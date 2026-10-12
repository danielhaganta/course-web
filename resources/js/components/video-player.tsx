import { useEffect, useState } from 'react';

const WATERMARK_MOVE_INTERVAL_MS = 5000;

type Position = { top: number; left: number };

// Dalam persen, dijaga supaya teks tidak keluar dari area video
function randomPosition(): Position {
    return {
        top: 5 + Math.random() * 80,
        left: 5 + Math.random() * 60,
    };
}

type Props = {
    embedUrl: string;
    title: string;
    watermark: string | null;
};

export default function VideoPlayer({ embedUrl, title, watermark }: Props) {
    const [position, setPosition] = useState<Position>(randomPosition);

    useEffect(() => {
        if (!watermark) {
            return;
        }

        const interval = setInterval(
            () => setPosition(randomPosition()),
            WATERMARK_MOVE_INTERVAL_MS,
        );

        return () => clearInterval(interval);
    }, [watermark]);

    return (
        <div
            className="relative aspect-video overflow-hidden rounded-[4px] bg-black"
            onContextMenu={(event) => event.preventDefault()}
        >
            <iframe
                src={embedUrl}
                title={title}
                className="absolute inset-0 size-full border-0"
                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
                allowFullScreen
            />
            {watermark && (
                <span
                    aria-hidden
                    className="pointer-events-none absolute text-xs whitespace-nowrap text-white/25 select-none"
                    style={{
                        top: `${position.top}%`,
                        left: `${position.left}%`,
                    }}
                >
                    {watermark}
                </span>
            )}
        </div>
    );
}
