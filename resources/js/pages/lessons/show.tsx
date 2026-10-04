import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import CourseSectionItem from '@/components/course-section-item';
import { Button } from '@/components/ui/button';
import VideoPlayer from '@/components/video-player';
import { show as showCourse } from '@/routes/courses';
import { show as showLesson } from '@/routes/lessons';
import type { ActiveLesson, CourseCurriculum, Lesson } from '@/types';

type Props = {
    course: CourseCurriculum;
    lesson: ActiveLesson;
    embedUrl: string | null;
    watermark: string | null;
};

export default function LessonShow({
    course,
    lesson,
    embedUrl,
    watermark,
}: Props) {
    const lessons = course.sections.flatMap((section) => section.lessons);
    const activeIndex = lessons.findIndex((item) => item.id === lesson.id);
    const previousLesson = lessons[activeIndex - 1];
    const nextLesson = lessons[activeIndex + 1];

    return (
        <>
            <Head title={lesson.title} />
            <div className="grid gap-8 lg:grid-cols-[1fr_340px]">
                <div className="min-w-0 space-y-6">
                    <LessonContent
                        lesson={lesson}
                        embedUrl={embedUrl}
                        watermark={watermark}
                    />

                    <div className="space-y-1">
                        <Link
                            href={showCourse(course.slug)}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            {course.title}
                        </Link>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {lesson.title}
                        </h1>
                    </div>

                    <nav className="flex justify-between gap-4">
                        <LessonNavButton
                            courseSlug={course.slug}
                            lesson={previousLesson}
                            direction="previous"
                        />
                        <LessonNavButton
                            courseSlug={course.slug}
                            lesson={nextLesson}
                            direction="next"
                        />
                    </nav>
                </div>

                <aside className="space-y-3">
                    <h2 className="font-semibold">Materi course</h2>
                    {course.sections.map((section) => (
                        <CourseSectionItem
                            key={section.id}
                            section={section}
                            courseSlug={course.slug}
                            activeLessonId={lesson.id}
                            defaultOpen={section.lessons.some(
                                (item) => item.id === lesson.id,
                            )}
                        />
                    ))}
                </aside>
            </div>
        </>
    );
}

function LessonContent({ lesson, embedUrl, watermark }: Omit<Props, 'course'>) {
    if (lesson.type === 'text') {
        if (!hasVisibleContent(lesson.content)) {
            return null;
        }

        return (
            <div
                className="space-y-3 rounded-lg border p-6 leading-relaxed [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:list-disc [&_ul]:pl-5"
                // Sudah disanitasi di backend
                dangerouslySetInnerHTML={{ __html: lesson.content ?? '' }}
            />
        );
    }

    if (!embedUrl) {
        return (
            <div className="flex aspect-video items-center justify-center rounded-lg bg-muted text-muted-foreground">
                Video belum tersedia.
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

type LessonNavButtonProps = {
    courseSlug: string;
    lesson: Lesson | undefined;
    direction: 'previous' | 'next';
};

function LessonNavButton({
    courseSlug,
    lesson,
    direction,
}: LessonNavButtonProps) {
    const label =
        direction === 'previous' ? 'Materi sebelumnya' : 'Materi berikutnya';
    const content =
        direction === 'previous' ? (
            <>
                <ChevronLeft /> {label}
            </>
        ) : (
            <>
                {label} <ChevronRight />
            </>
        );

    if (!lesson) {
        return (
            <Button variant="outline" disabled>
                {content}
            </Button>
        );
    }

    return (
        <Button variant="outline" asChild>
            <Link href={showLesson({ course: courseSlug, lesson: lesson.id })}>
                {content}
            </Link>
        </Button>
    );
}
