import { useEffect, useRef } from 'react';
import FootagePlaceholder from '@/components/footage-placeholder';
import { cn } from '@/lib/utils';

type Props = {
    // Isi videoSrc untuk memutar showreel otomatis tanpa suara
    videoSrc?: string | null;
    posterSrc?: string | null;
    className?: string;
};

export default function Showreel({ videoSrc, posterSrc, className }: Props) {
    const videoRef = useRef<HTMLVideoElement>(null);

    // Video tetap dipasang supaya poster tampil, tapi tidak diputar untuk pengguna yang mengurangi gerakan
    useEffect(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            videoRef.current?.pause();
        }
    }, []);

    if (videoSrc) {
        return (
            <video
                ref={videoRef}
                src={videoSrc}
                poster={posterSrc ?? undefined}
                className={cn('size-full object-cover', className)}
                autoPlay
                muted
                loop
                playsInline
                preload="metadata"
                aria-hidden
            />
        );
    }

    if (posterSrc) {
        return (
            <img
                src={posterSrc}
                alt=""
                className={cn('size-full object-cover', className)}
            />
        );
    }

    return <FootagePlaceholder look="graded" className={className} />;
}
