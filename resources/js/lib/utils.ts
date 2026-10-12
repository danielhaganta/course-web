import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

// Contoh: (1, 'lesson') -> "1 lesson", (3, 'lesson') -> "3 lessons"
export function pluralize(
    count: number,
    singular: string,
    plural = `${singular}s`,
): string {
    return `${count} ${count === 1 ? singular : plural}`;
}
