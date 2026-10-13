import { Head } from '@inertiajs/react';
import BeforeAfterSlider from '@/components/before-after-slider';
import { BEFORE_AFTER_EXAMPLES } from '@/lib/before-after-examples';
import { cn } from '@/lib/utils';

export default function BeforeAfter() {
    return (
        <>
            <Head title="Before / after">
                <meta
                    head-key="description"
                    name="description"
                    content="Drag to compare log footage with the graded result from the Poeima Academy instructor's work."
                />
            </Head>

            <div className="space-y-12">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h1 className="font-display text-[clamp(56px,7vw,120px)] leading-[0.85] font-black uppercase">
                        Before / after
                    </h1>
                    <p className="max-w-sm leading-relaxed text-muted-foreground">
                        Left is the log footage from the camera, right is the
                        final grade. Drag the line, or select the slider and use
                        the arrow keys.
                    </p>
                </div>

                <div className="grid gap-x-6 gap-y-12 md:grid-cols-2">
                    {BEFORE_AFTER_EXAMPLES.map((example, index) => (
                        <section
                            key={example.title}
                            aria-labelledby={`example-${index}-title`}
                            // Contoh pertama selebar halaman sebagai pembuka
                            className={cn(index === 0 && 'md:col-span-2')}
                        >
                            <BeforeAfterSlider
                                before={example.before}
                                after={example.after}
                                label={`Drag to compare before and after: ${example.title}`}
                                isCompact
                                className={cn(
                                    'aspect-[4/5] rounded-[2px] sm:aspect-video',
                                    index === 0 && 'md:aspect-[21/9]',
                                )}
                            />
                            <h2
                                id={`example-${index}-title`}
                                className="mt-4 text-xl font-bold"
                            >
                                {example.title}
                            </h2>
                        </section>
                    ))}
                </div>
            </div>
        </>
    );
}
