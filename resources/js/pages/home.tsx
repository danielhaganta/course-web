import { Head, Link } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import type { MouseEvent, ReactNode } from 'react';
import BeforeAfterSlider from '@/components/before-after-slider';
import FilmStrip from '@/components/film-strip';
import type { Still } from '@/components/film-strip';
import Showreel from '@/components/showreel';
import { Button } from '@/components/ui/button';
import { BEFORE_AFTER_EXAMPLES } from '@/lib/before-after-examples';
import { pluralize } from '@/lib/utils';
import { beforeAfter, pricing } from '@/routes';

type Props = {
    maxDevices: number;
};

const BEFORE_AFTER_SECTION_ID = 'before-after-preview';

// Ganti dengan file di public/, misalnya '/media/showreel.mp4', saat video sudah ada
const SHOWREEL = {
    videoSrc: null,
    posterSrc: null,
};

// Placeholder pita cuplikan, nanti diganti still dari karya pengajar
const STILLS: Still[] = [
    { src: null, tone: 'saturate(.7)' },
    { src: null, tone: 'hue-rotate(160deg) saturate(.35) brightness(.8)' },
    { src: null, tone: 'grayscale(.7) brightness(.85)' },
    { src: null, tone: 'sepia(.35) saturate(.6)' },
    { src: null, tone: 'brightness(.65) contrast(1.15)' },
    { src: null, tone: 'hue-rotate(-12deg) saturate(.5)' },
];

export default function Home({ maxDevices }: Props) {
    return (
        <>
            <Head title="DaVinci Resolve color grading course">
                <meta
                    head-key="description"
                    name="description"
                    content="Learn color grading in DaVinci Resolve through step-by-step video courses, from basic color correction to cinematic looks."
                />
            </Head>

            <Hero />

            <div className="mx-auto w-full max-w-6xl px-4 pt-24 pb-24 md:pt-32 md:pb-32">
                <BeforeAfterPreview />
                <Faq maxDevices={maxDevices} />
                <FinalCallToAction />
            </div>
        </>
    );
}

function scrollToBeforeAfter(event: MouseEvent<HTMLAnchorElement>) {
    const section = document.getElementById(BEFORE_AFTER_SECTION_ID);

    if (!section) {
        return;
    }

    event.preventDefault();

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    section.scrollIntoView({
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
    });
}

function Hero() {
    return (
        <section
            aria-labelledby="home-title"
            className="relative flex min-h-dvh flex-col justify-end overflow-hidden lg:h-dvh lg:min-h-[640px]"
        >
            <Showreel
                videoSrc={SHOWREEL.videoSrc}
                posterSrc={SHOWREEL.posterSrc}
                className="absolute inset-0"
            />
            {/* Gelap di kiri supaya judul tetap terbaca di atas video */}
            <div className="absolute inset-0 bg-gradient-to-r from-background/90 via-background/35 to-background/10" />

            <div className="relative max-w-[980px] px-6 pt-32 pb-12 lg:px-10 lg:pb-14">
                <p className="text-[13px] font-semibold tracking-[0.22em] uppercase">
                    Color grading · DaVinci Resolve
                </p>
                <h1
                    id="home-title"
                    className="mt-5 font-display text-[clamp(64px,9vw,150px)] leading-[0.84] font-black uppercase"
                >
                    Color that
                    <br />
                    tells stories.
                </h1>
                <div className="mt-9 flex flex-wrap items-center gap-4">
                    <Button asChild size="lg">
                        <Link href={pricing()}>Start learning</Link>
                    </Button>
                    <Button asChild size="lg" variant="outline">
                        <a
                            href={`#${BEFORE_AFTER_SECTION_ID}`}
                            onClick={scrollToBeforeAfter}
                        >
                            See before / after
                        </a>
                    </Button>
                </div>
            </div>

            <FilmStrip stills={STILLS} className="relative pb-6" />
        </section>
    );
}

function SectionHeading({ id, children }: { id: string; children: ReactNode }) {
    return (
        <h2
            id={id}
            className="font-display text-[clamp(44px,5.5vw,84px)] leading-[0.88] font-black uppercase"
        >
            {children}
        </h2>
    );
}

function BeforeAfterPreview() {
    const example = BEFORE_AFTER_EXAMPLES[0];

    return (
        <section
            id={BEFORE_AFTER_SECTION_ID}
            aria-labelledby="before-after-preview-title"
            className="scroll-mt-8"
        >
            <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading id="before-after-preview-title">
                    Drag. See the difference.
                </SectionHeading>
                <Button asChild variant="outline">
                    <Link href={beforeAfter()}>See more examples</Link>
                </Button>
            </div>
            <BeforeAfterSlider
                before={example.before}
                after={example.after}
                label={`Drag to compare before and after: ${example.title}`}
                isCompact
                className="mt-10 aspect-[4/5] rounded-[2px] sm:aspect-video"
            />
        </section>
    );
}

function Faq({ maxDevices }: { maxDevices: number }) {
    // Placeholder, dicek ulang oleh pemilik website
    const questions = [
        {
            question: 'What is the difference between Standard and Premium?',
            answer: 'Both plans unlock every regular course video. Premium adds exclusive lessons and practice file downloads.',
        },
        {
            question: 'Which software do I need?',
            answer: 'All lessons use DaVinci Resolve. [Confirm whether the free version is enough or DaVinci Resolve Studio is required.]',
        },
        {
            question: 'Can I download the videos?',
            answer: 'No. Videos are streamed only. Premium members can download the practice files used in the lessons.',
        },
        {
            question: 'How many devices can I use?',
            answer: `You can stay signed in on up to ${pluralize(maxDevices, 'device')} at the same time.`,
        },
        {
            question: 'Does my subscription renew automatically?',
            answer: 'No. You pay for each period, and we email you before your access ends so you can renew.',
        },
        {
            question: 'Can I buy a single course?',
            answer: 'Yes. Buy it once and keep it forever. Prices are on each course page.',
        },
    ];

    return (
        <section
            aria-labelledby="faq-title"
            className="mt-32 grid gap-10 md:mt-48 md:grid-cols-[1fr_1.4fr] md:gap-14"
        >
            <SectionHeading id="faq-title">Questions</SectionHeading>
            <div className="border-t">
                {questions.map((item) => (
                    <details key={item.question} className="group border-b">
                        <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 py-4 text-[17px] font-semibold outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring [&::-webkit-details-marker]:hidden">
                            {item.question}
                            <ChevronDown
                                aria-hidden
                                className="size-5 shrink-0 transition-transform group-open:rotate-180"
                            />
                        </summary>
                        <p className="pb-5 leading-relaxed text-foreground-soft">
                            {item.answer}
                        </p>
                    </details>
                ))}
            </div>
        </section>
    );
}

function FinalCallToAction() {
    return (
        <section
            aria-labelledby="final-cta-title"
            className="mt-24 flex flex-col gap-10 border-t pt-16 md:mt-32 md:flex-row md:items-end md:justify-between md:pt-20"
        >
            <h2
                id="final-cta-title"
                className="max-w-[14ch] min-w-0 font-display text-[clamp(56px,8vw,128px)] leading-[0.85] font-black uppercase"
            >
                Your next grade starts here.
            </h2>
            <Button asChild size="lg" className="self-start md:self-auto">
                <Link href={pricing()}>Start learning</Link>
            </Button>
        </section>
    );
}
