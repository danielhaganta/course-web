import FootagePlaceholder from '@/components/footage-placeholder';
import { cn } from '@/lib/utils';

export type Still = {
    src: string | null;
    // Variasi warna untuk placeholder selama belum ada gambar asli
    tone?: string;
};

type Props = {
    stills: Still[];
    className?: string;
};

/**
 * Pita cuplikan yang bergerak terus. Isinya dirender dua kali supaya loop tidak terputus.
 * Dekoratif, jadi disembunyikan dari pembaca layar.
 */
export default function FilmStrip({ stills, className }: Props) {
    return (
        <div aria-hidden className={cn('overflow-hidden', className)}>
            <div className="flex w-max gap-3.5 hover:[animation-play-state:paused] motion-safe:animate-marquee">
                {[...stills, ...stills].map((still, index) => (
                    <div
                        key={index}
                        className="h-[130px] w-[230px] shrink-0 overflow-hidden rounded-[2px]"
                    >
                        {still.src ? (
                            <img
                                src={still.src}
                                alt=""
                                loading="lazy"
                                className="size-full object-cover"
                            />
                        ) : (
                            <FootagePlaceholder
                                look="graded"
                                tone={still.tone}
                            />
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
