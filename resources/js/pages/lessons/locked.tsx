import { Head, Link } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { pricing } from '@/routes';
import { show as showCourse } from '@/routes/courses';

type Props = {
    course: { title: string; slug: string };
    lesson: { title: string; is_exclusive: boolean };
};

export default function LessonLocked({ course, lesson }: Props) {
    return (
        <>
            <Head title={lesson.title} />
            <div className="mx-auto max-w-2xl py-10 md:py-16">
                <div className="flex size-14 items-center justify-center rounded-full border">
                    <Lock className="size-5" aria-hidden />
                </div>
                <p className="mt-8 text-[13px] font-semibold tracking-[0.22em] uppercase">
                    {lesson.is_exclusive ? 'Khusus Premium' : 'Khusus member'}
                </p>
                <h1 className="mt-3 font-display text-[clamp(44px,6vw,72px)] leading-[0.92] font-extrabold uppercase">
                    {lesson.title}
                </h1>
                <p className="mt-6 max-w-xl text-lg leading-[1.65] text-foreground-soft">
                    {lesson.is_exclusive
                        ? `Materi ini khusus member Premium. Upgrade ke paket Premium untuk menonton materi exclusive di ${course.title}.`
                        : `Materi ini khusus member. Gabung membership untuk menonton semua materi di ${course.title}.`}
                </p>
                <div className="mt-9 flex flex-wrap gap-3">
                    <Button asChild size="lg">
                        <Link href={pricing()}>
                            {lesson.is_exclusive
                                ? 'Lihat paket Premium'
                                : 'Lihat paket membership'}
                        </Link>
                    </Button>
                    <Button asChild size="lg" variant="outline">
                        <Link href={showCourse(course.slug)}>
                            Kembali ke course
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
