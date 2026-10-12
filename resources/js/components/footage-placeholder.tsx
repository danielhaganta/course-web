import { cn } from '@/lib/utils';

// Pengganti gambar sementara: "log" meniru footage mentah yang datar, "graded" meniru hasil grading
const LOOKS = {
    log: 'linear-gradient(160deg, #8f8a82 0%, #7d7a74 35%, #6a6964 60%, #8c867c 100%)',
    graded: 'radial-gradient(120% 90% at 70% 30%, #f0b46a 0%, #c9773d 22%, #5a3a2a 45%, #163338 70%, #0b1416 100%)',
} as const;

type Props = {
    look: keyof typeof LOOKS;
    // CSS filter untuk variasi warna antar placeholder
    tone?: string;
    className?: string;
};

export default function FootagePlaceholder({ look, tone, className }: Props) {
    return (
        <div
            aria-hidden
            className={cn('size-full', className)}
            style={{ background: LOOKS[look], filter: tone }}
        />
    );
}
