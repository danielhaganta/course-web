import { Link } from '@inertiajs/react';
import { Download } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
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
        <section
            aria-labelledby="attachments-title"
            className="rounded-md border bg-secondary p-6"
        >
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 id="attachments-title" className="text-lg font-semibold">
                    File latihan
                </h2>
                {!canDownload && (
                    <Badge variant="outline">Khusus Premium</Badge>
                )}
            </div>
            <ul className="mt-4 space-y-2.5">
                {attachments.map((attachment) => (
                    <li
                        key={attachment.id}
                        className="flex flex-wrap items-center justify-between gap-3 rounded-[4px] bg-background px-4 py-3.5 text-[15px]"
                    >
                        <span className="min-w-0 break-words">
                            {attachment.name}
                        </span>
                        <span className="flex items-center gap-3">
                            <span className="text-sm text-muted-foreground tabular-nums">
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
                        </span>
                    </li>
                ))}
            </ul>
            {!canDownload && (
                <p className="mt-4 text-sm text-muted-foreground">
                    Khusus member Premium.{' '}
                    <Link
                        href={pricing()}
                        className="text-foreground underline underline-offset-4"
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
