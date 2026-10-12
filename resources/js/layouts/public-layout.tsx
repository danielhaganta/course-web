import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import SiteFooter from '@/components/site-footer';
import SiteHeader from '@/components/site-header';
import { cn } from '@/lib/utils';

// Halaman satu layar penuh di desktop, header melayang di atas gambar
const FULLSCREEN_PAGES = ['home', 'before-after', 'instructor'];

/**
 * Layout persisten: header tidak dimuat ulang saat pindah halaman,
 * hanya isi halaman yang masuk dengan fade dan sedikit zoom.
 */
export default function PublicLayout({ children }: { children: ReactNode }) {
    const { component, url } = usePage();
    const isFullscreen = FULLSCREEN_PAGES.includes(component);

    return (
        <div className="relative flex min-h-dvh flex-col overflow-x-clip bg-background text-foreground">
            <SiteHeader />
            <main
                key={url}
                className={cn(
                    'flex-1 motion-safe:animate-page-enter',
                    !isFullscreen &&
                        'mx-auto w-full max-w-6xl px-4 pt-28 pb-12 md:pt-32 md:pb-16',
                )}
            >
                {children}
            </main>
            {/* Di desktop halaman layar penuh tidak di-scroll, footer cukup di halaman lain */}
            <SiteFooter className={cn(isFullscreen && 'lg:hidden')} />
        </div>
    );
}
