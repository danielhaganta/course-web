import { Head, Link } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { show as showCourse } from '@/routes/courses';

type Props = {
    course: { title: string; slug: string };
    lesson: { title: string };
};

export default function LessonLocked({ course, lesson }: Props) {
    return (
        <>
            <Head title={lesson.title} />
            <div className="mx-auto flex max-w-lg flex-col items-center gap-4 rounded-lg border p-8 text-center">
                <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                    <Lock className="size-5" aria-hidden />
                </div>
                <div className="space-y-2">
                    <h1 className="text-xl font-semibold">{lesson.title}</h1>
                    <p className="text-muted-foreground">
                        Materi ini khusus member. Gabung membership untuk
                        menonton semua materi di {course.title}.
                    </p>
                </div>
                <div className="flex flex-col gap-2 sm:flex-row">
                    <Button asChild>
                        {/* Halaman pricing dibuat nanti */}
                        <Link href="/pricing">Lihat paket membership</Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={showCourse(course.slug)}>
                            Kembali ke course
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
