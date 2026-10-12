import { Head, Link } from '@inertiajs/react';
import FilmStrip from '@/components/film-strip';
import type { Still } from '@/components/film-strip';
import Showreel from '@/components/showreel';
import { Button } from '@/components/ui/button';
import { beforeAfter, pricing } from '@/routes';

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

export default function Home() {
    return (
        <>
            <Head title="DaVinci Resolve color grading course">
                <meta
                    head-key="description"
                    name="description"
                    content="Learn color grading in DaVinci Resolve through step-by-step video courses, from basic color correction to cinematic looks."
                />
            </Head>

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
                            <Link href={beforeAfter()}>See before / after</Link>
                        </Button>
                    </div>
                </div>

                <FilmStrip stills={STILLS} className="relative pb-6" />
            </section>
        </>
    );
}
