import { MoveHorizontal } from 'lucide-react';
import { useRef, useState } from 'react';
import type { KeyboardEvent, PointerEvent } from 'react';
import FootagePlaceholder from '@/components/footage-placeholder';
import { cn } from '@/lib/utils';

export type ComparisonImage = {
    // Null = placeholder, sampai ada gambar asli dari admin atau folder public
    src: string | null;
    alt: string;
    label: string;
    // CSS filter untuk variasi warna placeholder
    tone?: string;
};

type Props = {
    before: ComparisonImage;
    after: ComparisonImage;
    // Nama pembanding untuk pembaca layar, wajib dibedakan kalau ada beberapa slider di satu halaman
    label?: string;
    // Label Before/After rapat ke tepi, untuk slider yang tidak tertutup header
    isCompact?: boolean;
    className?: string;
};

const KEYBOARD_STEP = 2;
const KEYBOARD_PAGE_STEP = 10;

const clamp = (value: number) => Math.min(100, Math.max(0, value));

export default function BeforeAfterSlider({
    before,
    after,
    label,
    isCompact = false,
    className,
}: Props) {
    const containerRef = useRef<HTMLDivElement>(null);
    const isDragging = useRef(false);
    const [position, setPosition] = useState(50);

    function moveTo(clientX: number) {
        const rect = containerRef.current?.getBoundingClientRect();

        if (rect) {
            setPosition(clamp(((clientX - rect.left) / rect.width) * 100));
        }
    }

    function handlePointerDown(event: PointerEvent<HTMLDivElement>) {
        isDragging.current = true;
        event.currentTarget.setPointerCapture(event.pointerId);
        moveTo(event.clientX);
    }

    function handlePointerMove(event: PointerEvent<HTMLDivElement>) {
        if (isDragging.current) {
            moveTo(event.clientX);
        }
    }

    function stopDragging() {
        isDragging.current = false;
    }

    function handleKeyDown(event: KeyboardEvent<HTMLDivElement>) {
        const step = event.shiftKey ? KEYBOARD_PAGE_STEP : KEYBOARD_STEP;
        const nextPosition: Record<string, number> = {
            ArrowLeft: position - step,
            ArrowDown: position - step,
            ArrowRight: position + step,
            ArrowUp: position + step,
            PageDown: position - KEYBOARD_PAGE_STEP,
            PageUp: position + KEYBOARD_PAGE_STEP,
            Home: 0,
            End: 100,
        };

        if (event.key in nextPosition) {
            event.preventDefault();
            setPosition(clamp(nextPosition[event.key]));
        }
    }

    return (
        <div
            ref={containerRef}
            // pan-y: geser vertikal di HP tetap untuk scroll halaman
            className={cn(
                'relative cursor-ew-resize touch-pan-y overflow-hidden select-none',
                className,
            )}
            onPointerDown={handlePointerDown}
            onPointerMove={handlePointerMove}
            onPointerUp={stopDragging}
            onPointerCancel={stopDragging}
        >
            <ComparisonFrame image={before} look="log" />
            <div
                className="absolute inset-0"
                style={{ clipPath: `inset(0 0 0 ${position}%)` }}
            >
                <ComparisonFrame image={after} look="graded" />
            </div>

            <FrameLabel
                className={
                    isCompact
                        ? 'top-4 left-4'
                        : 'top-24 left-6 lg:top-28 lg:left-10'
                }
            >
                {before.label}
            </FrameLabel>
            <FrameLabel
                className={
                    isCompact
                        ? 'top-4 right-4'
                        : 'top-24 right-6 lg:top-28 lg:right-10'
                }
            >
                {after.label}
            </FrameLabel>

            <div
                className="absolute inset-y-0 w-0.5 -translate-x-1/2 bg-foreground"
                style={{ left: `${position}%` }}
            >
                <div
                    role="slider"
                    tabIndex={0}
                    aria-label={
                        label ??
                        `Drag to compare ${before.label.toLowerCase()} and ${after.label.toLowerCase()}`
                    }
                    aria-valuemin={0}
                    aria-valuemax={100}
                    aria-valuenow={Math.round(position)}
                    aria-valuetext={`${Math.round(100 - position)}% ${after.label.toLowerCase()} visible`}
                    onKeyDown={handleKeyDown}
                    className="absolute top-1/2 left-1/2 flex size-11 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-[3px] border-background bg-foreground text-background outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-foreground"
                >
                    <MoveHorizontal className="size-5" aria-hidden />
                </div>
            </div>
        </div>
    );
}

function ComparisonFrame({
    image,
    look,
}: {
    image: ComparisonImage;
    look: 'log' | 'graded';
}) {
    if (!image.src) {
        return (
            <FootagePlaceholder
                look={look}
                tone={image.tone}
                className="absolute inset-0"
            />
        );
    }

    return (
        <img
            src={image.src}
            alt={image.alt}
            draggable={false}
            className="absolute inset-0 size-full object-cover"
        />
    );
}

function FrameLabel({
    className,
    children,
}: {
    className: string;
    children: string;
}) {
    return (
        <span
            className={cn(
                'pointer-events-none absolute rounded-[3px] bg-background/50 px-2 py-1.5 text-xs font-medium tracking-[0.04em] text-foreground',
                className,
            )}
        >
            {children}
        </span>
    );
}
