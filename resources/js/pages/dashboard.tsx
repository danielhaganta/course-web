import { Head, Link } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard, pricing } from '@/routes';
import { show as showCourse } from '@/routes/courses';
import type { ActiveSubscription, PurchasedCourse } from '@/types';

type Props = {
    subscription: ActiveSubscription | null;
    purchasedCourses: PurchasedCourse[];
};

export default function Dashboard({ subscription, purchasedCourses }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <SubscriptionCard subscription={subscription} />

                <section className="space-y-3">
                    <h2 className="text-lg font-semibold">
                        Course yang dibeli
                    </h2>
                    {purchasedCourses.length === 0 ? (
                        <p className="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                            Belum ada course yang dibeli satuan.
                        </p>
                    ) : (
                        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {purchasedCourses.map((course) => (
                                <li key={course.slug}>
                                    <Link
                                        href={showCourse(course.slug)}
                                        className="block rounded-lg border p-4 font-medium hover:bg-accent"
                                    >
                                        {course.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

function SubscriptionCard({
    subscription,
}: {
    subscription: ActiveSubscription | null;
}) {
    return (
        <Card className="max-w-md">
            <CardHeader>
                <CardDescription>Status langganan</CardDescription>
                <CardTitle>
                    {subscription?.plan_name ?? 'Belum berlangganan'}
                </CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">
                {subscription ? (
                    <>Berlaku sampai {formatDate(subscription.ends_at)}</>
                ) : (
                    <>
                        Pilih paket untuk membuka semua course.{' '}
                        <Link
                            href={pricing()}
                            className="text-foreground underline"
                        >
                            Lihat paket
                        </Link>
                    </>
                )}
            </CardContent>
        </Card>
    );
}

function formatDate(isoDate: string): string {
    return new Date(isoDate).toLocaleDateString('id-ID', {
        dateStyle: 'long',
        timeZone: 'Asia/Jakarta',
    });
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
