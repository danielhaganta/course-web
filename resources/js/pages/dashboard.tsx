import { Head, Link, usePage } from '@inertiajs/react';
import FootagePlaceholder from '@/components/footage-placeholder';
import ProgressBar from '@/components/progress-bar';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/date';
import { pluralize } from '@/lib/utils';
import { dashboard, pricing } from '@/routes';
import { index as coursesIndex, show as showCourse } from '@/routes/courses';
import { show as showLesson } from '@/routes/lessons';
import type {
    ActiveSubscription,
    ContinueLearning,
    DashboardCourse,
    PurchasedCourse,
} from '@/types';

type Props = {
    subscription: ActiveSubscription | null;
    endedSubscription: ActiveSubscription | null;
    continueLearning: ContinueLearning | null;
    courses: DashboardCourse[];
    purchasedCourses: PurchasedCourse[];
};

// Tombol perpanjang muncul kalau sisa langganan di bawah batas ini
const RENEWAL_REMINDER_DAYS = 7;
const DAY_MS = 24 * 60 * 60 * 1000;

export default function Dashboard({
    subscription,
    endedSubscription,
    continueLearning,
    courses,
    purchasedCourses,
}: Props) {
    const { auth } = usePage().props;
    const firstName = auth.user.name.split(' ')[0];
    const isEmpty = !subscription && courses.length === 0;

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-10 p-4 sm:p-6 lg:p-8">
                <h1 className="font-display text-[clamp(40px,5vw,64px)] leading-[0.92] font-extrabold uppercase">
                    Hi, {firstName}
                </h1>

                {isEmpty ? (
                    <EmptyState endedSubscription={endedSubscription} />
                ) : (
                    <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                        {continueLearning ? (
                            <ContinueLearningCard course={continueLearning} />
                        ) : (
                            <StartLearningCard />
                        )}
                        <SubscriptionCard
                            subscription={subscription}
                            endedSubscription={endedSubscription}
                        />
                    </div>
                )}

                {courses.length > 0 && (
                    <section aria-labelledby="my-courses-title">
                        <SectionTitle id="my-courses-title">
                            Your courses
                        </SectionTitle>
                        <ul className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {courses.map((course) => (
                                <li key={course.slug}>
                                    <CourseProgressCard course={course} />
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {purchasedCourses.length > 0 && (
                    <section aria-labelledby="purchased-title">
                        <SectionTitle id="purchased-title">
                            Courses you bought
                        </SectionTitle>
                        <ul className="mt-5 divide-y rounded-md border">
                            {purchasedCourses.map((course) => (
                                <li key={course.slug}>
                                    <Link
                                        href={showCourse(course.slug)}
                                        className="flex flex-wrap items-center justify-between gap-2 px-5 py-4 transition-colors hover:bg-foreground/5"
                                    >
                                        <span className="font-medium">
                                            {course.title}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            Lifetime access
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}

function SectionTitle({ id, children }: { id: string; children: string }) {
    return (
        <h2 id={id} className="font-display text-3xl font-extrabold uppercase">
            {children}
        </h2>
    );
}

function CourseThumbnail({ src }: { src: string | null }) {
    return src ? (
        <img
            src={src}
            alt=""
            className="absolute inset-0 size-full object-cover"
        />
    ) : (
        <FootagePlaceholder
            look="graded"
            tone="saturate(.6)"
            className="absolute inset-0"
        />
    );
}

function ContinueLearningCard({ course }: { course: ContinueLearning }) {
    return (
        <section
            aria-labelledby="continue-title"
            className="flex flex-col overflow-hidden rounded-md border bg-card sm:flex-row"
        >
            <div className="relative aspect-video sm:aspect-auto sm:w-2/5">
                <CourseThumbnail src={course.thumbnail_url} />
            </div>
            <div className="flex flex-1 flex-col gap-5 p-6 sm:p-7">
                <div>
                    <p
                        id="continue-title"
                        className="text-xs font-semibold tracking-[0.18em] uppercase"
                    >
                        Continue learning
                    </p>
                    <h3 className="mt-3 font-display text-3xl leading-[0.95] font-extrabold uppercase">
                        {course.title}
                    </h3>
                </div>
                <CourseProgressLine course={course} />
                {course.next_lesson_id !== null && (
                    <Button asChild size="lg" className="mt-auto self-start">
                        <Link
                            href={showLesson({
                                course: course.slug,
                                lesson: course.next_lesson_id,
                            })}
                        >
                            Go to next lesson
                        </Link>
                    </Button>
                )}
            </div>
        </section>
    );
}

function StartLearningCard() {
    return (
        <section className="flex flex-col justify-between gap-6 rounded-md border bg-card p-6 sm:p-7">
            <div>
                <h2 className="font-display text-3xl font-extrabold uppercase">
                    Start learning
                </h2>
                <p className="mt-2 max-w-md text-foreground-soft">
                    Pick a course below. Lessons you mark as completed will show
                    up here.
                </p>
            </div>
        </section>
    );
}

function SubscriptionCard({
    subscription,
    endedSubscription,
}: {
    subscription: ActiveSubscription | null;
    endedSubscription: ActiveSubscription | null;
}) {
    if (!subscription) {
        return (
            <section className="flex flex-col gap-5 rounded-md border bg-card p-6 sm:p-7">
                <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                    Subscription
                </p>
                <div>
                    <h2 className="text-xl font-bold">
                        {endedSubscription
                            ? `${endedSubscription.plan_name} has ended`
                            : 'No subscription yet'}
                    </h2>
                    <p className="mt-1.5 text-sm text-foreground-soft">
                        {endedSubscription
                            ? `Ended ${formatDate(endedSubscription.ends_at)}.`
                            : 'Subscribe to unlock every course.'}
                    </p>
                </div>
                <Button asChild className="self-start">
                    <Link href={pricing()}>
                        {endedSubscription
                            ? 'Renew subscription'
                            : 'Choose a plan'}
                    </Link>
                </Button>
            </section>
        );
    }

    const daysLeft = Math.ceil(
        (new Date(subscription.ends_at).getTime() - Date.now()) / DAY_MS,
    );
    const isEndingSoon = daysLeft < RENEWAL_REMINDER_DAYS;

    return (
        <section className="flex flex-col gap-5 rounded-md border bg-card p-6 sm:p-7">
            <p className="text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase">
                Active subscription
            </p>
            <div>
                <h2 className="text-xl font-bold">{subscription.plan_name}</h2>
                <p className="mt-1.5 text-sm text-foreground-soft">
                    Valid until {formatDate(subscription.ends_at)}
                    {isEndingSoon &&
                        ` · ${pluralize(Math.max(daysLeft, 0), 'day')} left`}
                </p>
            </div>
            {isEndingSoon && (
                <Button asChild className="self-start">
                    <Link href={pricing()}>Renew subscription</Link>
                </Button>
            )}
        </section>
    );
}

function CourseProgressLine({ course }: { course: DashboardCourse }) {
    return (
        <div className="space-y-2">
            <p className="text-sm text-muted-foreground">
                {course.completed_lessons_count} of {course.lessons_count}{' '}
                lessons completed
            </p>
            <ProgressBar
                value={course.completed_lessons_count}
                max={course.lessons_count}
                label={`${course.title} progress`}
            />
        </div>
    );
}

function CourseProgressCard({ course }: { course: DashboardCourse }) {
    return (
        <Link
            href={showCourse(course.slug)}
            className="group block overflow-hidden rounded-md border bg-card outline-none focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
        >
            <div className="relative aspect-video overflow-hidden">
                <div className="absolute inset-0 transition-transform duration-500 group-hover:scale-[1.04]">
                    <CourseThumbnail src={course.thumbnail_url} />
                </div>
            </div>
            <div className="space-y-4 p-5">
                <h3 className="font-bold">{course.title}</h3>
                <CourseProgressLine course={course} />
            </div>
        </Link>
    );
}

function EmptyState({
    endedSubscription,
}: {
    endedSubscription: ActiveSubscription | null;
}) {
    return (
        <section className="rounded-md border bg-card p-7 sm:p-10">
            <h2 className="font-display text-4xl font-extrabold uppercase">
                {endedSubscription
                    ? 'Your subscription has ended'
                    : 'No courses yet'}
            </h2>
            <p className="mt-3 max-w-lg text-foreground-soft">
                {endedSubscription
                    ? `${endedSubscription.plan_name} ended on ${formatDate(endedSubscription.ends_at)}. Renew to unlock every course and your progress again.`
                    : 'Choose a plan to unlock every course, or buy just the course you need.'}
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
                <Button asChild size="lg">
                    <Link href={pricing()}>
                        {endedSubscription
                            ? 'Renew subscription'
                            : 'Choose a plan'}
                    </Link>
                </Button>
                <Button asChild size="lg" variant="outline">
                    <Link href={coursesIndex()}>Browse courses</Link>
                </Button>
            </div>
        </section>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
