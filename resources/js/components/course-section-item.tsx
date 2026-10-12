import { Link } from '@inertiajs/react';
import { Check, CirclePlay, FileText, Lock, Minus, Plus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { formatDuration, totalDuration } from '@/lib/duration';
import { show as showLesson } from '@/routes/lessons';
import type { CourseSection, Lesson } from '@/types';

type Props = {
    section: CourseSection;
    courseSlug: string;
    completedLessonIds: number[];
    defaultOpen?: boolean;
};

export default function CourseSectionItem({
    section,
    courseSlug,
    completedLessonIds,
    defaultOpen = false,
}: Props) {
    return (
        <Collapsible
            defaultOpen={defaultOpen}
            className="border-b last:border-b-0"
        >
            <CollapsibleTrigger className="group flex w-full items-center justify-between gap-4 bg-secondary px-6 py-5 text-left outline-none focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring">
                <span>
                    <span className="block text-lg font-bold">
                        {section.title}
                    </span>
                    <span className="mt-1.5 block text-[13px] text-muted-foreground">
                        {section.lessons.length} materi ·{' '}
                        {formatDuration(totalDuration(section.lessons))}
                    </span>
                </span>
                <Plus
                    className="size-5 shrink-0 group-data-[state=open]:hidden"
                    aria-hidden
                />
                <Minus
                    className="size-5 shrink-0 group-data-[state=closed]:hidden"
                    aria-hidden
                />
            </CollapsibleTrigger>
            <CollapsibleContent>
                <ul>
                    {section.lessons.map((lesson) => (
                        <LessonRow
                            key={lesson.id}
                            lesson={lesson}
                            courseSlug={courseSlug}
                            isCompleted={completedLessonIds.includes(lesson.id)}
                        />
                    ))}
                </ul>
            </CollapsibleContent>
        </Collapsible>
    );
}

function LessonRow({
    lesson,
    courseSlug,
    isCompleted,
}: {
    lesson: Lesson;
    courseSlug: string;
    isCompleted: boolean;
}) {
    const Icon = lesson.type === 'video' ? CirclePlay : FileText;

    return (
        <li className="border-t border-border/60">
            <Link
                href={showLesson({ course: courseSlug, lesson: lesson.id })}
                className="flex items-center gap-3.5 px-6 py-4 text-[15px] transition-colors outline-none hover:bg-foreground/5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring"
            >
                <Icon
                    className="size-[18px] shrink-0 text-muted-foreground"
                    aria-label={lesson.type === 'video' ? 'Video' : 'Teks'}
                />
                <span className="min-w-0 flex-1">{lesson.title}</span>
                {lesson.is_preview && <Badge>Preview</Badge>}
                {lesson.is_exclusive && (
                    <Badge variant="outline">Premium</Badge>
                )}
                {!lesson.can_watch && (
                    <Lock
                        className="size-4 shrink-0 text-muted-foreground"
                        aria-label="Terkunci"
                    />
                )}
                {isCompleted && (
                    <Check className="size-4 shrink-0" aria-label="Selesai" />
                )}
                <span className="w-12 shrink-0 text-right text-[13px] text-muted-foreground tabular-nums">
                    {formatDuration(lesson.duration_seconds)}
                </span>
            </Link>
        </li>
    );
}
