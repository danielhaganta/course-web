import { Head, Link, usePage } from '@inertiajs/react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import CourseSectionItem from '@/components/course-section-item';
import { Button } from '@/components/ui/button';
import { useCheckout } from '@/hooks/use-checkout';
import { formatRupiah } from '@/lib/currency';
import { formatDuration, totalDuration } from '@/lib/duration';
import { pricing, register } from '@/routes';
import { show as showLesson } from '@/routes/lessons';
import type { CourseDetail } from '@/types';

type Props = {
    course: CourseDetail;
};

export default function CourseShow({ course }: Props) {
    const lessons = course.sections.flatMap((section) => section.lessons);

    return (
        <>
            <Head title={course.title} />
            <div className="grid gap-10 lg:grid-cols-[1fr_320px]">
                <div className="space-y-8">
                    <div className="space-y-3">
                        <h1 className="text-3xl font-semibold tracking-tight">
                            {course.title}
                        </h1>
                        {course.summary && (
                            <p className="text-lg text-muted-foreground">
                                {course.summary}
                            </p>
                        )}
                        <p className="text-sm text-muted-foreground">
                            {lessons.length} materi ·{' '}
                            {formatDuration(totalDuration(lessons))}
                        </p>
                    </div>

                    {course.description && (
                        <div
                            className="space-y-3 leading-relaxed [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5"
                            // Sudah disanitasi di backend
                            dangerouslySetInnerHTML={{
                                __html: course.description,
                            }}
                        />
                    )}

                    <section className="space-y-3">
                        <h2 className="text-xl font-semibold">Materi course</h2>
                        {course.sections.map((section, index) => (
                            <CourseSectionItem
                                key={section.id}
                                section={section}
                                courseSlug={course.slug}
                                defaultOpen={index === 0}
                            />
                        ))}
                    </section>
                </div>

                <aside className="space-y-4 lg:sticky lg:top-8 lg:self-start">
                    {course.thumbnail_url && (
                        <img
                            src={course.thumbnail_url}
                            alt={course.title}
                            className="aspect-video w-full rounded-lg border object-cover"
                        />
                    )}
                    {course.can_access ? (
                        lessons[0] && (
                            <Button asChild size="lg" className="w-full">
                                <Link
                                    href={showLesson({
                                        course: course.slug,
                                        lesson: lessons[0].id,
                                    })}
                                >
                                    Mulai belajar
                                </Link>
                            </Button>
                        )
                    ) : (
                        <>
                            <Button asChild size="lg" className="w-full">
                                <Link href={pricing()}>
                                    Lihat paket membership
                                </Link>
                            </Button>
                            {course.price !== null && (
                                <BuyCourseButton
                                    slug={course.slug}
                                    price={course.price}
                                />
                            )}
                        </>
                    )}
                </aside>
            </div>
        </>
    );
}

// Hanya tampil kalau user belum punya akses ke course ini
function BuyCourseButton({ slug, price }: { slug: string; price: number }) {
    const { auth } = usePage().props;
    const { startCheckout, isPreparing, error } = useCheckout();
    const label = `Beli course ini · ${formatRupiah(price)}`;

    if (!auth.user) {
        return (
            <Button asChild size="lg" variant="outline" className="w-full">
                <Link href={register()}>{label}</Link>
            </Button>
        );
    }

    return (
        <div className="space-y-2">
            <Button
                size="lg"
                variant="outline"
                className="w-full"
                disabled={isPreparing}
                onClick={() =>
                    startCheckout(CheckoutController.course.url(slug))
                }
            >
                {isPreparing ? 'Menyiapkan pembayaran...' : label}
            </Button>
            {error && (
                <p role="alert" className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}
