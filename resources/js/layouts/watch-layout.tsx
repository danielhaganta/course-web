import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import ProgressBar from '@/components/progress-bar';
import { home } from '@/routes';
import { show as showCourse } from '@/routes/courses';
import type { CourseCurriculum } from '@/types';

type WatchPageProps = {
    course: CourseCurriculum;
    completedLessonIds: number[];
};

/**
 * Layout ringkas halaman nonton. Persisten, jadi header tidak dimuat ulang saat pindah materi.
 */
export default function WatchLayout({ children }: { children: ReactNode }) {
    const { auth, course, completedLessonIds } =
        usePage<WatchPageProps>().props;
    const totalLessons = course.sections.reduce(
        (total, section) => total + section.lessons.length,
        0,
    );

    return (
        <div className="min-h-dvh bg-background text-foreground">
            <header className="border-b">
                <div className="mx-auto flex max-w-[1400px] flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-8">
                    <div className="flex min-w-0 flex-wrap items-baseline gap-x-5 gap-y-1">
                        <Link
                            href={home()}
                            className="font-display text-[26px] font-extrabold tracking-[0.06em]"
                        >
                            POIEMA
                        </Link>
                        <Link
                            href={showCourse(course.slug)}
                            className="truncate text-[15px] text-foreground-soft hover:text-foreground"
                        >
                            {course.title}
                        </Link>
                    </div>
                    {auth.user && (
                        <div className="flex items-center gap-3.5 text-sm text-muted-foreground">
                            <span>
                                {completedLessonIds.length} of {totalLessons}{' '}
                                lessons completed
                            </span>
                            <ProgressBar
                                value={completedLessonIds.length}
                                max={totalLessons}
                                label="Course progress"
                                className="w-36"
                            />
                        </div>
                    )}
                </div>
            </header>
            <main className="mx-auto max-w-[1400px] px-4 pt-7 pb-20 sm:px-8">
                {children}
            </main>
        </div>
    );
}
