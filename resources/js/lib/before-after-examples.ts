import type { ComparisonImage } from '@/components/before-after-slider';

export type BeforeAfterExample = {
    title: string;
    before: ComparisonImage;
    after: ComparisonImage;
};

/**
 * Placeholder sampai ada cuplikan asli. Ganti src dengan path gambar di public/,
 * misalnya '/media/before-after/portrait-before.jpg', lalu hapus tone.
 * Contoh pertama juga tampil di beranda.
 */
export const BEFORE_AFTER_EXAMPLES: BeforeAfterExample[] = [
    {
        title: 'Warm portrait',
        before: {
            src: null,
            alt: 'Portrait in log footage before grading',
            label: 'Before',
        },
        after: {
            src: null,
            alt: 'The same portrait after a warm grade',
            label: 'After',
        },
    },
    {
        title: 'Teal night street',
        before: {
            src: null,
            alt: 'Night street in log footage before grading',
            label: 'Before',
            tone: 'brightness(.8)',
        },
        after: {
            src: null,
            alt: 'The same street after a teal night grade',
            label: 'After',
            tone: 'hue-rotate(160deg) saturate(.6) brightness(.75)',
        },
    },
    {
        title: 'Faded film look',
        before: {
            src: null,
            alt: 'Outdoor scene in log footage before grading',
            label: 'Before',
            tone: 'brightness(1.05)',
        },
        after: {
            src: null,
            alt: 'The same scene after a faded film grade',
            label: 'After',
            tone: 'sepia(.45) saturate(.55) contrast(.9)',
        },
    },
];
