import { Head, Link } from '@inertiajs/react';
import CourseSectionItem from '@/components/course-section-item';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/currency';
import { formatDuration, totalDuration } from '@/lib/duration';
import { pricing } from '@/routes';
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
                                <Button
                                    size="lg"
                                    variant="outline"
                                    className="w-full"
                                    disabled
                                >
                                    Beli course ini ·{' '}
                                    {formatRupiah(course.price)}
                                </Button>
                            )}
                        </>
                    )}
                </aside>
            </div>
        </>
    );
}
