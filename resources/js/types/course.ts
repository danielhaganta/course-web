export type LessonType = 'video' | 'text';

export type Lesson = {
    id: number;
    title: string;
    type: LessonType;
    duration_seconds: number;
    is_preview: boolean;
    can_watch: boolean;
};

export type CourseSection = {
    id: number;
    title: string;
    lessons: Lesson[];
};

export type CourseSummary = {
    title: string;
    slug: string;
    summary: string | null;
    thumbnail_url: string | null;
    lessons_count: number;
};

export type CourseDetail = {
    title: string;
    slug: string;
    summary: string | null;
    description: string | null;
    thumbnail_url: string | null;
    sections: CourseSection[];
};

export type CourseCurriculum = {
    title: string;
    slug: string;
    sections: CourseSection[];
};

export type ActiveLesson = {
    id: number;
    title: string;
    type: LessonType;
    duration_seconds: number;
    content: string | null;
};
