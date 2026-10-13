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

// Tanpa Home, wordmark sudah menjadi link ke beranda
const MENU: MenuItem[] = [
    { label: 'Course', href: coursesIndex(), matchChildren: true },
    { label: 'Instructor', href: instructor() },
    { label: 'Pricing', href: pricing() },
];

export default function SiteHeader() {
    const [isMenuOpen, setMenuOpen] = useState(false);
    const closeMenu = () => setMenuOpen(false);

    return (
        // Pill mengambang selebar isinya, terpisah dari tepi layar
        <header className="fixed top-4 left-1/2 z-30 flex w-max max-w-[calc(100%-2rem)] -translate-x-1/2 items-center gap-6 rounded-full border bg-background/75 py-1.5 pr-1.5 pl-5 backdrop-blur-md lg:gap-8">
            <Wordmark />

            <nav aria-label="Main menu" className="hidden lg:block">
                <MenuLinks className="flex gap-6" />
            </nav>

            <div className="hidden items-center gap-4 lg:flex">
                <AccountActions />
            </div>

            <Sheet open={isMenuOpen} onOpenChange={setMenuOpen}>
                <SheetTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-11 rounded-full lg:hidden"
                        aria-label="Open menu"
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
                        Poeima Academy main navigation
                    </SheetDescription>
                    <nav aria-label="Main menu">
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
            <span className="font-display text-2xl font-black tracking-[0.08em]">
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
                                'inline-flex min-h-11 items-center text-[13px] font-semibold tracking-[0.14em] whitespace-nowrap uppercase decoration-2 underline-offset-[6px] transition-colors outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring',
                                isActive
                                    ? 'text-foreground underline'
                                    : 'text-muted-foreground hover:text-foreground',
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
                    className="text-sm whitespace-nowrap text-muted-foreground transition-colors hover:text-foreground"
                >
                    Log in
                </Link>
                <Button asChild className="rounded-full">
                    <Link href={register()} onClick={onNavigate}>
                        Join
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
                    className="text-sm whitespace-nowrap text-muted-foreground transition-colors hover:text-foreground"
                >
                    Admin panel
                </a>
            )}
            <Button asChild className="rounded-full">
                <Link href={dashboard()} onClick={onNavigate}>
                    Dashboard
                </Link>
            </Button>
        </>
    );
}
