import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Check, ChevronLeft, ChevronRight } from 'lucide-react';
import LessonProgressController from '@/actions/App/Http/Controllers/LessonProgressController';
import LessonAttachments from '@/components/lesson-attachments';
import LessonSidebar from '@/components/lesson-sidebar';
import { Button } from '@/components/ui/button';
import VideoPlayer from '@/components/video-player';
import { cn } from '@/lib/utils';
import { show as showLesson } from '@/routes/lessons';
import type {
    ActiveLesson,
    CourseCurriculum,
    Lesson,
    LessonAttachment,
} from '@/types';

type Props = {
    course: CourseCurriculum;
    lesson: ActiveLesson;
    embedUrl: string | null;
    watermark: string | null;
    attachments: LessonAttachment[];
    canDownload: boolean;
    completedLessonIds: number[];
};

export default function LessonShow({
    course,
    lesson,
    embedUrl,
    watermark,
    attachments,
    canDownload,
    completedLessonIds,
}: Props) {
    const { auth } = usePage().props;
    const lessons = course.sections.flatMap((section) => section.lessons);
    const activeIndex = lessons.findIndex((item) => item.id === lesson.id);
    const sectionIndex = course.sections.findIndex((section) =>
        section.lessons.some((item) => item.id === lesson.id),
    );
    const lessonNumber =
        course.sections[sectionIndex]?.lessons.findIndex(
            (item) => item.id === lesson.id,
        ) ?? -1;

    return (
        <>
            <Head title={`${lesson.title} · ${course.title}`} />

            {/* HP: player, daftar materi, lalu file latihan. Desktop: daftar materi di kolom kanan */}
            <div className="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:grid-rows-[auto_1fr]">
                <div className="min-w-0 lg:col-start-1">
                    <LessonContent
                        lesson={lesson}
                        embedUrl={embedUrl}
                        watermark={watermark}
                    />

                    <div className="mt-7 flex flex-wrap items-start justify-between gap-5">
                        <div className="min-w-0">
                            <p className="text-sm text-muted-foreground">
                                Section {sectionIndex + 1} · Lesson{' '}
                                {lessonNumber + 1}
                            </p>
                            <h1 className="mt-2 font-display text-[clamp(36px,4vw,52px)] leading-none font-extrabold uppercase">
                                {lesson.title}
                            </h1>
                        </div>
                        {auth.user && (
                            <CompletionToggle
                                courseSlug={course.slug}
                                lessonId={lesson.id}
                                isCompleted={completedLessonIds.includes(
                                    lesson.id,
                                )}
                            />
                        )}
                    </div>

                    <nav
                        aria-label="Lesson navigation"
                        className="mt-6 flex flex-wrap gap-3"
                    >
                        <LessonNavButton
                            courseSlug={course.slug}
                            lesson={lessons[activeIndex - 1]}
                            direction="previous"
                        />
                        <LessonNavButton
                            courseSlug={course.slug}
                            lesson={lessons[activeIndex + 1]}
                            direction="next"
                        />
                    </nav>
                </div>

                <div className="lg:col-start-2 lg:row-span-2 lg:row-start-1">
                    <LessonSidebar
                        course={course}
                        activeLessonId={lesson.id}
                        completedLessonIds={completedLessonIds}
                    />
                </div>

                {attachments.length > 0 && (
                    <div className="min-w-0 lg:col-start-1">
                        <LessonAttachments
                            attachments={attachments}
                            canDownload={canDownload}
                        />
                    </div>
                )}
            </div>
        </>
    );
}

function CompletionToggle({
    courseSlug,
    lessonId,
    isCompleted,
}: {
    courseSlug: string;
    lessonId: number;
    isCompleted: boolean;
}) {
    return (
        <Form
            {...LessonProgressController.update.form({
                course: courseSlug,
                lesson: lessonId,
            })}
            options={{ preserveScroll: true }}
        >
            {({ processing }) => (
                <>
                    <input
                        type="hidden"
                        name="completed"
                        value={isCompleted ? '0' : '1'}
                    />
                    <Button
                        type="submit"
                        size="lg"
                        variant={isCompleted ? 'default' : 'outline'}
                        aria-pressed={isCompleted}
                        disabled={processing}
                    >
                        <Check
                            className={cn(!isCompleted && 'opacity-40')}
                            aria-hidden
                        />
                        {isCompleted ? 'Completed' : 'Mark as completed'}
                    </Button>
                </>
            )}
        </Form>
    );
}

function LessonContent({
    lesson,
    embedUrl,
    watermark,
}: Pick<Props, 'lesson' | 'embedUrl' | 'watermark'>) {
    if (lesson.type === 'text') {
        if (!hasVisibleContent(lesson.content)) {
            return null;
        }

        return (
            <div
                className="space-y-4 rounded-md border bg-card p-6 leading-[1.7] text-foreground-soft sm:p-8 [&_a]:underline [&_h2]:font-semibold [&_h2]:text-foreground [&_h3]:font-semibold [&_h3]:text-foreground [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5"
                // Sudah disanitasi di backend
                dangerouslySetInnerHTML={{ __html: lesson.content ?? '' }}
            />
        );
    }

    if (!embedUrl) {
        return (
            <div className="flex aspect-video items-center justify-center rounded-[4px] bg-card text-muted-foreground">
                Video not available yet.
            </div>
        );
    }

    return (
        <VideoPlayer
            embedUrl={embedUrl}
            title={lesson.title}
            watermark={watermark}
        />
    );
}

// Rich editor menyimpan konten kosong sebagai "<p></p>", bukan null
function hasVisibleContent(html: string | null): boolean {
    if (!html) {
        return false;
    }

    if (/<(img|iframe|video)\b/i.test(html)) {
        return true;
    }

    return html.replace(/<[^>]*>|&nbsp;/g, '').trim() !== '';
}

function LessonNavButton({
    courseSlug,
    lesson,
    direction,
}: {
    courseSlug: string;
    lesson: Lesson | undefined;
    direction: 'previous' | 'next';
}) {
    const isNext = direction === 'next';
    const content = isNext ? (
        <>
            Next lesson <ChevronRight />
        </>
    ) : (
        <>
            <ChevronLeft /> Previous lesson
        </>
    );
    const variant = isNext ? 'default' : 'outline';

    if (!lesson) {
        return (
            <Button variant={variant} disabled>
                {content}
            </Button>
        );
    }

    return (
        <Button variant={variant} asChild>
            <Link href={showLesson({ course: courseSlug, lesson: lesson.id })}>
                {content}
            </Link>
        </Button>
    );
}
