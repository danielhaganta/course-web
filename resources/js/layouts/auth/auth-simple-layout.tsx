import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center bg-background px-4 py-10">
            <Link
                href={home()}
                className="flex items-baseline gap-2 rounded-[2px] outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
            >
                <span className="font-display text-3xl font-black tracking-[0.08em]">
                    POEIMA
                </span>
                <span className="text-xs tracking-[0.14em] text-muted-foreground uppercase">
                    Academy
                </span>
            </Link>

            <main className="mt-8 w-full max-w-md rounded-md border bg-card p-6 sm:p-8">
                <div className="mb-7 space-y-2">
                    <h1 className="font-display text-4xl leading-none font-extrabold uppercase">
                        {title}
                    </h1>
                    {description && (
                        <p className="text-sm leading-relaxed text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                {children}
            </main>
        </div>
    );
}
