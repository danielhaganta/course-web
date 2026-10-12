import { Head, Link, usePage } from '@inertiajs/react';
import { Play } from 'lucide-react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import CourseSectionItem from '@/components/course-section-item';
import FootagePlaceholder from '@/components/footage-placeholder';
import { Button } from '@/components/ui/button';
import { useCheckout } from '@/hooks/use-checkout';
import { formatRupiah } from '@/lib/currency';
import { formatDuration, totalDuration } from '@/lib/duration';
import { home, pricing, register } from '@/routes';
import { index as coursesIndex } from '@/routes/courses';
import { show as showLesson } from '@/routes/lessons';
import type { CourseDetail } from '@/types';

type Props = {
    course: CourseDetail;
};

export default function CourseShow({ course }: Props) {
    const lessons = course.sections.flatMap((section) => section.lessons);

    return (
        <>
            <Head title={course.title}>
                {course.summary && (
                    <meta
                        head-key="description"
                        name="description"
                        content={course.summary}
                    />
                )}
            </Head>

            <nav
                aria-label="Breadcrumb"
                className="mb-7 text-sm text-muted-foreground"
            >
                <ol className="flex flex-wrap gap-1.5">
                    <li>
                        <Link href={home()} className="hover:text-foreground">
                            Home
                        </Link>{' '}
                        /
                    </li>
                    <li>
                        <Link
                            href={coursesIndex()}
                            className="hover:text-foreground"
                        >
                            Courses
                        </Link>{' '}
                        /
                    </li>
                    <li aria-current="page" className="text-foreground-soft">
                        {course.title}
                    </li>
                </ol>
            </nav>

            <div className="grid items-start gap-10 lg:grid-cols-[1fr_380px] lg:gap-14">
                <div className="min-w-0">
                    <h1 className="font-display text-[clamp(48px,6vw,84px)] leading-[0.92] font-extrabold uppercase">
                        {course.title}
                    </h1>
                    {course.summary && (
                        <p className="mt-6 max-w-[620px] text-lg leading-[1.65] text-foreground-soft">
                            {course.summary}
                        </p>
                    )}
                    <dl className="mt-9 flex flex-wrap gap-9">
                        <Stat label="Sections" value={course.sections.length} />
                        <Stat label="Lessons" value={lessons.length} />
                        <Stat
                            label="Total duration"
                            value={formatDuration(totalDuration(lessons))}
                        />
                    </dl>
                </div>

                <CourseActionsCard course={course} />
            </div>

            <section
                aria-labelledby="curriculum-title"
                className="mt-24 max-w-[820px]"
            >
                <h2
                    id="curriculum-title"
                    className="font-display text-[56px] leading-none font-extrabold uppercase"
                >
                    Curriculum
                </h2>
                <p className="mt-2 mb-7 text-muted-foreground">
                    Lessons marked Preview can be watched without a
                    subscription.
                </p>
                <div className="overflow-hidden rounded-md border">
                    {course.sections.map((section, index) => (
                        <CourseSectionItem
                            key={section.id}
                            section={section}
                            courseSlug={course.slug}
                            completedLessonIds={course.completed_lesson_ids}
                            defaultOpen={index < 2}
                        />
                    ))}
                </div>
            </section>

            {course.description && (
                <section
                    aria-labelledby="description-title"
                    className="mt-20 max-w-[820px]"
                >
                    <h2
                        id="description-title"
                        className="font-display text-4xl font-extrabold uppercase"
                    >
                        About this course
                    </h2>
                    <div
                        className="mt-5 space-y-4 leading-[1.7] text-foreground-soft [&_a]:underline [&_h2]:font-semibold [&_h2]:text-foreground [&_h3]:font-semibold [&_h3]:text-foreground [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5"
                        // Sudah disanitasi di backend
                        dangerouslySetInnerHTML={{ __html: course.description }}
                    />
                </section>
            )}
        </>
    );
}

function Stat({ label, value }: { label: string; value: string | number }) {
    return (
        <div className="flex flex-col-reverse">
            <dt className="mt-1 text-[13px] text-muted-foreground">{label}</dt>
            <dd className="font-display text-[40px] leading-none font-extrabold">
                {value}
            </dd>
        </div>
    );
}

function CourseActionsCard({ course }: { course: CourseDetail }) {
    const previewLesson = course.sections
        .flatMap((section) => section.lessons)
        .find((lesson) => lesson.is_preview);
    const hasStarted = course.completed_lesson_ids.length > 0;

    return (
        <aside className="overflow-hidden rounded-md border bg-secondary lg:sticky lg:top-28">
            <CourseMedia course={course} previewLessonId={previewLesson?.id} />

            <div className="space-y-3 p-7">
                {course.can_access && course.resume_lesson_id !== null ? (
                    <Button asChild size="lg" className="w-full">
                        <Link
                            href={showLesson({
                                course: course.slug,
                                lesson: course.resume_lesson_id,
                            })}
                        >
                            {hasStarted
                                ? 'Continue learning'
                                : 'Start learning'}
                        </Link>
                    </Button>
                ) : (
                    !course.can_access && (
                        <>
                            <Button asChild size="lg" className="w-full">
                                <Link href={pricing()}>
                                    Join the membership
                                </Link>
                            </Button>
                            {course.price !== null && (
                                <BuyCourseButton
                                    slug={course.slug}
                                    price={course.price}
                                />
                            )}
                        </>
                    )
                )}

                <ul className="space-y-3 pt-3 text-sm text-foreground-soft">
                    {course.price !== null && (
                        <li>Lifetime access when bought on its own</li>
                    )}
                    <li>Practice files for Premium members</li>
                    <li>Learning progress saved automatically</li>
                </ul>
            </div>
        </aside>
    );
}

// Thumbnail course, dan jadi tautan ke materi preview pertama kalau ada
function CourseMedia({
    course,
    previewLessonId,
}: {
    course: CourseDetail;
    previewLessonId: number | undefined;
}) {
    const image = course.thumbnail_url ? (
        <img
            src={course.thumbnail_url}
            alt=""
            className="absolute inset-0 size-full object-cover"
        />
    ) : (
        <FootagePlaceholder
            look="graded"
            tone="saturate(.6)"
            className="absolute inset-0"
        />
    );

    if (previewLessonId === undefined) {
        return <div className="relative aspect-video">{image}</div>;
    }

    return (
        <Link
            href={showLesson({ course: course.slug, lesson: previewLessonId })}
            aria-label="Watch the preview lesson"
            className="group relative block aspect-video outline-none focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring"
        >
            {image}
            <span className="absolute top-1/2 left-1/2 flex size-[72px] -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-background/70 transition-colors group-hover:bg-background/85">
                <Play className="size-6 fill-foreground" aria-hidden />
            </span>
        </Link>
    );
}

function BuyCourseButton({ slug, price }: { slug: string; price: number }) {
    const { auth } = usePage().props;
    const { startCheckout, isPreparing, error } = useCheckout();
    const label = `Buy this course · ${formatRupiah(price)}`;

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
                {isPreparing ? 'Preparing payment...' : label}
            </Button>
            {error && (
                <p role="alert" className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}
