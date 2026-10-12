import { Head } from '@inertiajs/react';
import BeforeAfterSlider from '@/components/before-after-slider';
import type { ComparisonImage } from '@/components/before-after-slider';

// Isi src dengan gambar dari folder public atau dari admin saat cuplikan asli sudah ada
const BEFORE: ComparisonImage = {
    src: null,
    alt: 'Footage log sebelum grading',
    label: 'Sebelum',
};

const AFTER: ComparisonImage = {
    src: null,
    alt: 'Footage yang sama setelah grading',
    label: 'Sesudah',
};

export default function BeforeAfter() {
    return (
        <>
            <Head title="Before / after">
                <meta
                    head-key="description"
                    name="description"
                    content="Geser dan bandingkan footage log dengan hasil grading dari karya pengajar Poeima Academy."
                />
            </Head>

            <section
                aria-labelledby="before-after-title"
                className="relative h-[100svh] min-h-[560px] lg:h-dvh lg:min-h-[640px]"
            >
                <BeforeAfterSlider
                    before={BEFORE}
                    after={AFTER}
                    className="absolute inset-0"
                />

                {/* Tidak menangkap klik, supaya slider tetap bisa digeser dari area teks */}
                <div className="pointer-events-none absolute inset-x-6 bottom-8 flex flex-wrap items-end justify-between gap-6 lg:inset-x-10 lg:bottom-10">
                    <h1
                        id="before-after-title"
                        className="font-display text-[clamp(48px,6vw,96px)] leading-[0.88] font-black uppercase [text-shadow:0_2px_30px_rgb(0_0_0/0.4)]"
                    >
                        Geser.
                        <br />
                        Lihat bedanya.
                    </h1>
                    <p className="max-w-sm text-sm leading-relaxed text-foreground-soft">
                        Kiri footage log dari kamera, kanan hasil akhirnya.
                        Geser garisnya, atau pakai tombol panah setelah memilih
                        slider.
                    </p>
                </div>
            </section>
        </>
    );
}
