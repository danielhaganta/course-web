import { Link } from '@inertiajs/react';
import { Download, FileArchive } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { pricing } from '@/routes';
import { download } from '@/routes/attachments';
import type { LessonAttachment } from '@/types';

type Props = {
    attachments: LessonAttachment[];
    canDownload: boolean;
};

export default function LessonAttachments({ attachments, canDownload }: Props) {
    if (attachments.length === 0) {
        return null;
    }

    return (
        <section className="space-y-3">
            <h2 className="font-semibold">File latihan</h2>
            <ul className="divide-y rounded-lg border">
                {attachments.map((attachment) => (
                    <li
                        key={attachment.id}
                        className="flex flex-wrap items-center gap-3 p-4 text-sm"
                    >
                        <FileArchive
                            className="size-4 shrink-0 text-muted-foreground"
                            aria-hidden
                        />
                        <span className="min-w-0 flex-1 break-words">
                            {attachment.name}
                        </span>
                        <span className="text-muted-foreground tabular-nums">
                            {formatFileSize(attachment.size_bytes)}
                        </span>
                        {canDownload ? (
                            <Button asChild size="sm" variant="outline">
                                {/* <a> biasa: respons berupa file, bukan halaman Inertia */}
                                <a href={download.url(attachment.id)}>
                                    <Download /> Download
                                </a>
                            </Button>
                        ) : (
                            <Button size="sm" variant="outline" disabled>
                                <Download /> Download
                            </Button>
                        )}
                    </li>
                ))}
            </ul>
            {!canDownload && (
                <p className="text-sm text-muted-foreground">
                    Khusus member Premium.{' '}
                    <Link
                        href={pricing()}
                        className="text-foreground underline"
                    >
                        Lihat paket
                    </Link>
                </p>
            )}
        </section>
    );
}

// Contoh: 5_242_880 -> "5 MB", 2_147_483_648 -> "2 GB"
function formatFileSize(bytes: number): string {
    const megabytes = bytes / 1024 ** 2;
    const format = (value: number) =>
        value.toLocaleString('id-ID', { maximumFractionDigits: 1 });

    if (megabytes >= 1024) {
        return `${format(megabytes / 1024)} GB`;
    }

    return `${format(Math.max(megabytes, 0.1))} MB`;
}
