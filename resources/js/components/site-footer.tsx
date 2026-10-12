import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { contact, privacy, terms } from '@/routes';

const LINKS = [
    { label: 'Terms & conditions', href: terms() },
    { label: 'Privacy policy', href: privacy() },
    { label: 'Contact', href: contact() },
];

export default function SiteFooter({ className }: { className?: string }) {
    return (
        <footer
            className={cn(
                'flex flex-col gap-4 border-t px-6 py-8 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between lg:px-10',
                className,
            )}
        >
            <p>© {new Date().getFullYear()} Poeima Academy</p>
            <nav aria-label="Information">
                <ul className="flex flex-wrap gap-x-6 gap-y-2">
                    {LINKS.map((link) => (
                        <li key={link.label}>
                            <Link
                                href={link.href}
                                className="transition-colors hover:text-foreground"
                            >
                                {link.label}
                            </Link>
                        </li>
                    ))}
                </ul>
            </nav>
        </footer>
    );
}
