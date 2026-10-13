import { cn } from '@/lib/utils';

// Warna di token --footage-* pada app.css
const LOOKS = {
    log: 'var(--footage-log)',
    graded: 'var(--footage-graded)',
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
