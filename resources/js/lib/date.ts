const TIME_ZONE = 'Asia/Jakarta';

// en-US dipakai karena en-GB menulis September sebagai "Sept"
const dateFormat = new Intl.DateTimeFormat('en-US', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    timeZone: TIME_ZONE,
});

const timeFormat = new Intl.DateTimeFormat('en-GB', {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: TIME_ZONE,
});

// Contoh: "12 Oct 2026"
export function formatDate(isoDate: string): string {
    const parts = Object.fromEntries(
        dateFormat
            .formatToParts(new Date(isoDate))
            .map((part) => [part.type, part.value]),
    );

    return `${parts.day} ${parts.month} ${parts.year}`;
}

// Contoh: "12 Oct 2026, 14:30 WIB"
export function formatDateTime(isoDate: string): string {
    return `${formatDate(isoDate)}, ${timeFormat.format(new Date(isoDate))} WIB`;
}
