import { Link } from '@inertiajs/react';
import { ChevronDown, CirclePlay, FileText, Lock } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { formatDuration, totalDuration } from '@/lib/duration';
import { cn } from '@/lib/utils';
import { show as showLesson } from '@/routes/lessons';
import type { CourseSection, Lesson } from '@/types';

type Props = {
    section: CourseSection;
    courseSlug: string;
    activeLessonId?: number;
    defaultOpen?: boolean;
};

export default function CourseSectionItem({
    section,
    courseSlug,
    activeLessonId,
    defaultOpen = false,
}: Props) {
    return (
        <Collapsible
            defaultOpen={defaultOpen}
            className="rounded-lg border bg-card"
        >
            <CollapsibleTrigger className="group flex w-full items-center gap-3 p-4 text-left">
                <div className="flex-1">
                    <h3 className="font-medium">{section.title}</h3>
                    <p className="text-sm text-muted-foreground">
                        {section.lessons.length} materi ·{' '}
                        {formatDuration(totalDuration(section.lessons))}
                    </p>
                </div>
                <ChevronDown
                    className="size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=open]:rotate-180"
                    aria-hidden
                />
            </CollapsibleTrigger>
            <CollapsibleContent>
                <ul className="divide-y border-t">
                    {section.lessons.map((lesson) => (
                        <LessonRow
                            key={lesson.id}
                            lesson={lesson}
                            courseSlug={courseSlug}
                            isActive={lesson.id === activeLessonId}
                        />
                    ))}
                </ul>
            </CollapsibleContent>
        </Collapsible>
    );
}

type LessonRowProps = {
    lesson: Lesson;
    courseSlug: string;
    isActive: boolean;
};

function LessonRow({ lesson, courseSlug, isActive }: LessonRowProps) {
    const Icon = lesson.type === 'video' ? CirclePlay : FileText;

    return (
        <li>
            <Link
                href={showLesson({ course: courseSlug, lesson: lesson.id })}
                aria-current={isActive ? 'page' : undefined}
                className={cn(
                    'flex items-center gap-3 px-4 py-3 text-sm hover:bg-accent',
                    isActive && 'bg-accent font-medium',
                )}
            >
                <Icon
                    className="size-4 shrink-0 text-muted-foreground"
                    aria-label={lesson.type === 'video' ? 'Video' : 'Teks'}
                />
                <span className="flex-1">{lesson.title}</span>
                {lesson.is_preview && (
                    <Badge variant="secondary">Preview gratis</Badge>
                )}
                {lesson.is_exclusive && <Badge>Premium</Badge>}
                {!lesson.can_watch && (
                    <Lock
                        className="size-3.5 shrink-0 text-muted-foreground"
                        aria-label="Khusus member"
                    />
                )}
                <span className="shrink-0 text-muted-foreground tabular-nums">
                    {formatDuration(lesson.duration_seconds)}
                </span>
            </Link>
        </li>
    );
}
