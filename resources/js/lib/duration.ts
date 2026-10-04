import type { Lesson } from '@/types';

// Contoh: 240 -> "4m", 7260 -> "2j 1m"
export function formatDuration(seconds: number): string {
    const totalMinutes = Math.round(seconds / 60);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours === 0) {
        return `${minutes}m`;
    }

    return minutes === 0 ? `${hours}j` : `${hours}j ${minutes}m`;
}

export function totalDuration(lessons: Lesson[]): number {
    return lessons.reduce(
        (total, lesson) => total + lesson.duration_seconds,
        0,
    );
}
