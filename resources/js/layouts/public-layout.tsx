import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import SiteFooter from '@/components/site-footer';
import SiteHeader from '@/components/site-header';
import { cn } from '@/lib/utils';

// Tanpa container, header melayang di atas gambar
const FULL_BLEED_PAGES = ['home', 'instructor'];

// Satu layar penuh di desktop dan tidak di-scroll
const SINGLE_SCREEN_PAGES = ['instructor'];

/**
 * Layout persisten: header tidak dimuat ulang saat pindah halaman,
 * hanya isi halaman yang masuk dengan fade dan sedikit zoom.
 */
export default function PublicLayout({ children }: { children: ReactNode }) {
    const { component, url } = usePage();
    const isFullBleed = FULL_BLEED_PAGES.includes(component);

    return (
        <div className="relative flex min-h-dvh flex-col overflow-x-clip bg-background text-foreground">
            <SiteHeader />
            <main
                key={url}
                className={cn(
                    'flex-1 motion-safe:animate-page-enter',
                    !isFullBleed &&
                        'mx-auto w-full max-w-6xl px-4 pt-28 pb-12 md:pt-32 md:pb-16',
                )}
            >
                {children}
            </main>
            {/* Footer cukup di halaman yang di-scroll */}
            <SiteFooter
                className={cn(
                    SINGLE_SCREEN_PAGES.includes(component) && 'lg:hidden',
                )}
            />
        </div>
    );
}
