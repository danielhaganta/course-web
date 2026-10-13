import { Pause, Play } from 'lucide-react';
import { useState } from 'react';
import FootagePlaceholder from '@/components/footage-placeholder';
import { Button } from '@/components/ui/button';
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
 * Pitanya dekoratif (disembunyikan dari pembaca layar), tombol pause tetap bisa dijangkau (WCAG 2.2.2).
 */
export default function FilmStrip({ stills, className }: Props) {
    const [isPaused, setPaused] = useState(false);

    return (
        <div className={cn('relative', className)}>
            <div aria-hidden className="overflow-hidden">
                <div
                    className={cn(
                        'flex w-max gap-3.5 hover:[animation-play-state:paused] motion-safe:animate-marquee',
                        isPaused && '[animation-play-state:paused]',
                    )}
                >
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
            <Button
                type="button"
                variant="secondary"
                size="icon"
                aria-label={isPaused ? 'Play film strip' : 'Pause film strip'}
                onClick={() => setPaused(!isPaused)}
                className="absolute right-6 bottom-9 size-11 rounded-full bg-background/80 hover:bg-background motion-reduce:hidden lg:right-10"
            >
                {isPaused ? (
                    <Play aria-hidden className="size-4 fill-current" />
                ) : (
                    <Pause aria-hidden className="size-4 fill-current" />
                )}
            </Button>
        </div>
    );
}
