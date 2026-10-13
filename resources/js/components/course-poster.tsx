import { Link } from '@inertiajs/react';
import FootagePlaceholder from '@/components/footage-placeholder';
import ProgressBar from '@/components/progress-bar';
import { formatDuration } from '@/lib/duration';
import { pluralize } from '@/lib/utils';
import { show } from '@/routes/courses';
import type { CourseSummary } from '@/types';

type Props = {
    course: CourseSummary;
    // h3 kalau kartu berada di bawah judul h2
    titleAs?: 'h2' | 'h3';
};

export default function CoursePoster({ course, titleAs: Title = 'h2' }: Props) {
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
                <Title className="font-display text-[38px] leading-[0.95] font-extrabold [overflow-wrap:anywhere] uppercase">
                    {course.title}
                </Title>
                <p className="mt-3 text-[13px] text-foreground-soft">
                    {pluralize(course.lessons_count, 'lesson')} ·{' '}
                    {formatDuration(course.duration_seconds)}
                </p>
                {course.completed_lessons_count !== null && (
                    <div className="mt-4 space-y-2">
                        <p className="text-xs font-semibold tracking-[0.12em] uppercase">
                            {course.completed_lessons_count}/
                            {course.lessons_count} lessons completed
                        </p>
                        <ProgressBar
                            value={course.completed_lessons_count}
                            max={course.lessons_count}
                            label={`${course.title} progress`}
                        />
                    </div>
                )}
            </div>
        </Link>
    );
}
