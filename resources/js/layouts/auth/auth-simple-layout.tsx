import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

/**
 * Split layout: panel tipografi di kiri (desktop), form tanpa kartu di kanan.
 * Panel kiri bisa diganti still footage asli nanti.
 */
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh bg-background lg:grid-cols-[1.1fr_1fr]">
            <div
                aria-hidden
                className="hidden flex-col justify-end border-r bg-card p-12 lg:flex"
            >
                <p className="max-w-[9ch] font-display text-[clamp(72px,7vw,120px)] leading-[0.84] font-black uppercase">
                    Color that tells stories.
                </p>
            </div>

            <div className="flex flex-col px-6 py-6 sm:px-10 lg:px-16">
                <header className="flex items-center justify-between gap-4">
                    <Link
                        href={home()}
                        className="flex items-baseline gap-2 rounded-[2px] outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                    >
                        <span className="font-display text-2xl font-black tracking-[0.08em]">
                            POEIMA
                        </span>
                        <span className="text-xs tracking-[0.14em] text-muted-foreground uppercase">
                            Academy
                        </span>
                    </Link>
                    <Link
                        href={home()}
                        className="inline-flex min-h-11 items-center gap-1.5 text-sm whitespace-nowrap text-muted-foreground transition-colors outline-none hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                    >
                        <ArrowLeft aria-hidden className="size-4" />
                        Back to site
                    </Link>
                </header>

                <main className="mx-auto my-auto w-full max-w-sm py-12">
                    <div className="mb-8 space-y-3">
                        <h1 className="font-display text-5xl leading-none font-extrabold uppercase">
                            {title}
                        </h1>
                        {description && (
                            <p className="leading-relaxed text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </div>
                    {children}
                </main>
            </div>
        </div>
    );
}
