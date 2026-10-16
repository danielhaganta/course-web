import { Head } from '@inertiajs/react';
import CoursePoster from '@/components/course-poster';
import { cn } from '@/lib/utils';
import type { CourseSummary } from '@/types';

type Props = {
    courses: CourseSummary[];
};

export default function CoursesIndex({ courses }: Props) {
    return (
        <>
            <Head title="Courses">
                <meta
                    head-key="description"
                    name="description"
                    content="Color grading courses at Poeima Academy. Pick a course to see its curriculum and free preview lessons."
                />
            </Head>

            <div className="space-y-10">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h1 className="font-display text-[clamp(56px,7vw,120px)] leading-[0.85] font-black uppercase">
                        Courses
                    </h1>
                    <p className="max-w-sm leading-relaxed text-muted-foreground">
                        Pick a course to see its curriculum. Lessons marked
                        Preview can be watched without a subscription.
                    </p>
                </div>

                {courses.length === 0 ? (
                    <p className="rounded-md border border-dashed p-8 text-center text-muted-foreground">
                        No courses available yet.
                    </p>
                ) : (
                    <ul
                        // Dua course atau kurang tampil lebih besar supaya grid tidak kosong sepertiga
                        className={cn(
                            'grid gap-6 sm:grid-cols-2',
                            courses.length > 2 && 'lg:grid-cols-3',
                        )}
                    >
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
