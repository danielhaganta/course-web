import { useEffect, useRef, useState } from 'react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

const VISIBLE_THRESHOLD = 0.15;

/**
 * Section yang fade in sekali saat masuk layar.
 * Tanpa animasi untuk pengguna yang mengurangi gerakan (kelas motion-safe).
 */
export default function RevealSection({
    className,
    ...props
}: ComponentProps<'section'>) {
    const ref = useRef<HTMLElement>(null);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const element = ref.current;

        if (!element) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsVisible(true);
                    observer.disconnect();
                }
            },
            { threshold: VISIBLE_THRESHOLD },
        );

        observer.observe(element);

        return () => observer.disconnect();
    }, []);

    return (
        <section
            ref={ref}
            className={cn(
                'motion-safe:transition motion-safe:duration-700 motion-safe:ease-out',
                !isVisible && 'motion-safe:translate-y-6 motion-safe:opacity-0',
                className,
            )}
            {...props}
        />
    );
}
