import { cn } from '@/lib/utils';

type Props = {
    value: number;
    max: number;
    label: string;
    className?: string;
};

export default function ProgressBar({ value, max, label, className }: Props) {
    const percent = max > 0 ? Math.round((value / max) * 100) : 0;

    return (
        <div
            role="progressbar"
            aria-label={label}
            aria-valuemin={0}
            aria-valuemax={max}
            aria-valuenow={value}
            className={cn(
                'h-1.5 overflow-hidden rounded-[3px] bg-border',
                className,
            )}
        >
            <div
                className="h-full bg-foreground"
                style={{ width: `${percent}%` }}
            />
        </div>
    );
}
