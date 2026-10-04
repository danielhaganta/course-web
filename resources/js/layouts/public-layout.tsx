import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { dashboard, home, login, register } from '@/routes';
import { index as coursesIndex } from '@/routes/courses';

export default function PublicLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { auth } = usePage().props;

    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b">
                <div className="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4">
                    <Link href={home()} className="font-semibold">
                        Course
                    </Link>
                    <nav className="flex flex-1 items-center text-sm">
                        <Link
                            href={coursesIndex()}
                            className="text-muted-foreground hover:text-foreground"
                        >
                            Courses
                        </Link>
                    </nav>
                    <div className="flex items-center gap-2">
                        {auth.user ? (
                            <Button asChild size="sm">
                                <Link href={dashboard()}>Dashboard</Link>
                            </Button>
                        ) : (
                            <>
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={login()}>Login</Link>
                                </Button>
                                <Button asChild size="sm">
                                    <Link href={register()}>Register</Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-8 md:py-12">
                {children}
            </main>
        </div>
    );
}
