import { Head, Link } from '@inertiajs/react';
import { ImageIcon } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show } from '@/routes/courses';
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
                    content="Daftar course color grading di Poeima Academy. Pilih course untuk melihat kurikulum dan materi preview gratis."
                />
            </Head>
            <div className="space-y-8">
                <div className="space-y-2">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Courses
                    </h1>
                    <p className="text-muted-foreground">
                        Pilih course dan mulai belajar.
                    </p>
                </div>

                {courses.length === 0 ? (
                    <p className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                        Belum ada course yang tersedia.
                    </p>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <CourseCard key={course.slug} course={course} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

function CourseCard({ course }: { course: CourseSummary }) {
    return (
        <Link
            href={show(course)}
            className="group rounded-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            <Card className="h-full gap-4 overflow-hidden pt-0 transition-shadow group-hover:shadow-md">
                {course.thumbnail_url ? (
                    <img
                        src={course.thumbnail_url}
                        alt={course.title}
                        className="aspect-video w-full object-cover"
                    />
                ) : (
                    <div className="flex aspect-video w-full items-center justify-center bg-muted text-muted-foreground">
                        <ImageIcon className="size-8" aria-hidden />
                    </div>
                )}
                <CardHeader>
                    <CardTitle className="leading-snug">
                        {course.title}
                    </CardTitle>
                    {course.summary && (
                        <CardDescription className="line-clamp-3">
                            {course.summary}
                        </CardDescription>
                    )}
                </CardHeader>
                <CardContent className="mt-auto text-sm text-muted-foreground">
                    {course.lessons_count} materi
                </CardContent>
            </Card>
        </Link>
    );
}
