import { Head, Link } from '@inertiajs/react';
import FootagePlaceholder from '@/components/footage-placeholder';
import ProgressBar from '@/components/progress-bar';
import { formatDuration } from '@/lib/duration';
import { show } from '@/routes/courses';
import type { CourseSummary } from '@/types';

type Props = {
    courses: CourseSummary[];
};

export default function CoursesIndex({ courses }: Props) {
    return (
        <>
            <Head title="Course">
                <meta
                    head-key="description"
                    name="description"
                    content="Daftar course color grading di Poeima Academy. Pilih course untuk melihat kurikulum dan materi preview gratis."
                />
            </Head>

            <div className="space-y-10">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h1 className="font-display text-[clamp(56px,7vw,120px)] leading-[0.85] font-black uppercase">
                        Course
                    </h1>
                    <p className="max-w-sm leading-relaxed text-muted-foreground">
                        Pilih course untuk melihat kurikulumnya. Materi bertanda
                        Preview bisa ditonton tanpa langganan.
                    </p>
                </div>

                {courses.length === 0 ? (
                    <p className="rounded-md border border-dashed p-8 text-center text-muted-foreground">
                        Belum ada course yang tersedia.
                    </p>
                ) : (
                    <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <li key={course.slug}>
                                <CoursePoster course={course} />
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}

function CoursePoster({ course }: { course: CourseSummary }) {
    return (
        <Link
            href={show(course.slug)}
            className="group relative block aspect-[3/4] overflow-hidden rounded-[2px] outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
        >
            <div className="absolute inset-0 transition-transform duration-500 ease-out group-hover:scale-[1.04] group-focus-visible:scale-[1.04]">
                {course.thumbnail_url ? (
                    <img
                        src={course.thumbnail_url}
                        alt=""
                        loading="lazy"
                        className="size-full object-cover"
                    />
                ) : (
                    <FootagePlaceholder look="graded" tone="saturate(.6)" />
                )}
            </div>
            <div className="absolute inset-0 bg-gradient-to-t from-background/95 via-background/40 to-transparent" />

            <div className="absolute inset-x-5 bottom-5">
                <h2 className="font-display text-[38px] leading-[0.95] font-extrabold uppercase">
                    {course.title}
                </h2>
                <p className="mt-3 text-[13px] text-foreground-soft">
                    {course.lessons_count} materi ·{' '}
                    {formatDuration(course.duration_seconds)}
                </p>
                {course.completed_lessons_count !== null && (
                    <div className="mt-4 space-y-2">
                        <p className="text-xs font-semibold tracking-[0.12em] uppercase">
                            {course.completed_lessons_count}/
                            {course.lessons_count} materi selesai
                        </p>
                        <ProgressBar
                            value={course.completed_lessons_count}
                            max={course.lessons_count}
                            label={`Progres ${course.title}`}
                        />
                    </div>
                )}
            </div>
        </Link>
    );
}
