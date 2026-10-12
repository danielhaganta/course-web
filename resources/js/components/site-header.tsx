import { Link, usePage } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import {
    beforeAfter,
    dashboard,
    home,
    instructor,
    login,
    pricing,
    register,
} from '@/routes';
import { index as coursesIndex } from '@/routes/courses';
import { dashboard as adminDashboard } from '@/routes/filament/admin/pages';

type MenuItem = {
    label: string;
    href: NonNullable<InertiaLinkProps['href']>;
    // Course tetap aktif di halaman detail dan halaman nonton
    matchChildren?: boolean;
};

const MENU: MenuItem[] = [
    { label: 'Beranda', href: home() },
    { label: 'Before / After', href: beforeAfter() },
    { label: 'Course', href: coursesIndex(), matchChildren: true },
    { label: 'Pengajar', href: instructor() },
    { label: 'Paket', href: pricing() },
];

export default function SiteHeader() {
    const [isMenuOpen, setMenuOpen] = useState(false);
    const closeMenu = () => setMenuOpen(false);

    return (
        <header className="absolute inset-x-0 top-0 z-30 flex items-center justify-between gap-8 bg-gradient-to-b from-background/85 to-transparent px-6 py-4 lg:px-10">
            <Wordmark />

            <nav aria-label="Menu utama" className="hidden lg:block">
                <MenuLinks className="flex gap-7" />
            </nav>

            <div className="hidden items-center gap-5 lg:flex">
                <AccountActions />
            </div>

            <Sheet open={isMenuOpen} onOpenChange={setMenuOpen}>
                <SheetTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="lg:hidden"
                        aria-label="Buka menu"
                    >
                        <Menu className="size-6" />
                    </Button>
                </SheetTrigger>
                <SheetContent
                    side="right"
                    className="w-full max-w-sm gap-0 border-l bg-background px-6 pt-16 pb-8"
                >
                    <SheetTitle className="sr-only">Menu</SheetTitle>
                    <SheetDescription className="sr-only">
                        Navigasi utama Poeima Academy
                    </SheetDescription>
                    <nav aria-label="Menu utama">
                        <MenuLinks
                            className="flex flex-col"
                            itemClassName="py-3 font-display text-4xl font-extrabold tracking-normal"
                            onNavigate={closeMenu}
                        />
                    </nav>
                    <div className="mt-auto flex flex-col gap-4 border-t pt-6">
                        <AccountActions onNavigate={closeMenu} />
                    </div>
                </SheetContent>
            </Sheet>
        </header>
    );
}

function Wordmark() {
    return (
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
    );
}

function MenuLinks({
    className,
    itemClassName,
    onNavigate,
}: {
    className: string;
    itemClassName?: string;
    onNavigate?: () => void;
}) {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <ul className={className}>
            {MENU.map((item) => {
                const isActive = item.matchChildren
                    ? isCurrentOrParentUrl(item.href)
                    : isCurrentUrl(item.href);

                return (
                    <li key={item.label}>
                        <Link
                            href={item.href}
                            onClick={onNavigate}
                            aria-current={isActive ? 'page' : undefined}
                            className={cn(
                                'inline-flex min-h-11 items-center border-b-2 py-3 text-[13px] font-semibold tracking-[0.14em] uppercase transition-colors outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring',
                                isActive
                                    ? 'border-foreground text-foreground'
                                    : 'border-transparent text-muted-foreground hover:text-foreground',
                                itemClassName,
                            )}
                        >
                            {item.label}
                        </Link>
                    </li>
                );
            })}
        </ul>
    );
}

function AccountActions({ onNavigate }: { onNavigate?: () => void }) {
    const { auth } = usePage().props;

    if (!auth.user) {
        return (
            <>
                <Link
                    href={login()}
                    onClick={onNavigate}
                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    Masuk
                </Link>
                <Button asChild>
                    <Link href={register()} onClick={onNavigate}>
                        Gabung
                    </Link>
                </Button>
            </>
        );
    }

    return (
        <>
            {auth.user.is_admin && (
                // Panel Filament bukan halaman Inertia
                <a
                    href={adminDashboard.url()}
                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    Admin panel
                </a>
            )}
            <Button asChild>
                <Link href={dashboard()} onClick={onNavigate}>
                    Dashboard
                </Link>
            </Button>
        </>
    );
}
