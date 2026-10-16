import type { ComparisonImage } from '@/components/before-after-slider';

export type BeforeAfterExample = {
    title: string;
    before: ComparisonImage;
    after: ComparisonImage;
};

const pair = (
    number: number,
    title: string,
    scene: string,
): BeforeAfterExample => ({
    title,
    before: {
        src: `/media/before-after/${number}-before.jpg`,
        alt: `${scene} in log footage before grading`,
        label: 'Before',
    },
    after: {
        src: `/media/before-after/${number}-after.jpg`,
        alt: `The same ${scene.toLowerCase()} after grading`,
        label: 'After',
    },
});

// File di public/media/before-after, nomor 1 juga tampil di beranda
export const BEFORE_AFTER_EXAMPLES: BeforeAfterExample[] = [
    pair(1, 'Morning commute', 'Train platform'),
    pair(2, 'Empty station at night', 'Station'),
    pair(3, 'Behind the bar', 'Bartender close-up'),
];
