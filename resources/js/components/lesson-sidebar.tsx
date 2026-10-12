import { Link } from '@inertiajs/react';
import { Check, Lock } from 'lucide-react';
import { formatDuration } from '@/lib/duration';
import { cn } from '@/lib/utils';
import { show as showLesson } from '@/routes/lessons';
import type { CourseCurriculum, Lesson } from '@/types';

type Props = {
    course: CourseCurriculum;
    activeLessonId: number;
    completedLessonIds: number[];
};

export default function LessonSidebar({
    course,
    activeLessonId,
    completedLessonIds,
}: Props) {
    return (
        <aside
            aria-label="Daftar materi"
            className="overflow-hidden rounded-md border"
        >
            {course.sections.map((section) => (
                <div key={section.id}>
                    <p className="border-b bg-secondary px-5 py-4 text-[15px] font-bold">
                        {section.title}
                        <span className="mt-1 block text-xs font-medium text-muted-foreground">
                            {section.lessons.length} materi
                        </span>
                    </p>
                    <ul>
                        {section.lessons.map((lesson) => (
                            <LessonItem
                                key={lesson.id}
                                lesson={lesson}
                                courseSlug={course.slug}
                                isActive={lesson.id === activeLessonId}
                                isCompleted={completedLessonIds.includes(
                                    lesson.id,
                                )}
                            />
                        ))}
                    </ul>
                </div>
            ))}
        </aside>
    );
}

function LessonItem({
    lesson,
    courseSlug,
    isActive,
    isCompleted,
}: {
    lesson: Lesson;
    courseSlug: string;
    isActive: boolean;
    isCompleted: boolean;
}) {
    return (
        <li
            className={cn(
                'border-b border-border/60',
                isActive && 'bg-foreground/[0.07]',
            )}
        >
            <Link
                href={showLesson({ course: courseSlug, lesson: lesson.id })}
                aria-current={isActive ? 'page' : undefined}
                preserveScroll
                className="flex items-start gap-3 px-5 py-3.5 text-sm outline-none hover:bg-foreground/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring"
            >
                {/* Kotak progres, hanya penanda */}
                <span
                    className={cn(
                        'mt-px flex size-[18px] shrink-0 items-center justify-center rounded-[3px] border-[1.5px]',
                        isCompleted
                            ? 'border-foreground bg-foreground text-background'
                            : 'border-muted-foreground/60',
                    )}
                >
                    {isCompleted && (
                        <Check
                            className="size-3"
                            strokeWidth={3.5}
                            aria-label="Selesai"
                        />
                    )}
                </span>
                <span className="min-w-0 flex-1">
                    {lesson.title}
                    <span className="mt-1 block text-xs text-muted-foreground">
                        {formatDuration(lesson.duration_seconds)}
                    </span>
                </span>
                {!lesson.can_watch && (
                    <Lock
                        className="mt-0.5 size-3.5 shrink-0 text-muted-foreground"
                        aria-label="Terkunci"
                    />
                )}
            </Link>
        </li>
    );
}
