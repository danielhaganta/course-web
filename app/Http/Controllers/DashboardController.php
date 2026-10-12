<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AccessService;
use App\Services\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, AccessService $access, CourseProgress $progress): Response
    {
        $user = $request->user();
        $completedCounts = $progress->completedCountsByCourse($user);

        // Kalau ada beberapa yang aktif, tampilkan yang paling lama berlaku
        $subscription = $user->activeSubscriptions()
            ->with('plan')
            ->latest('ends_at')
            ->first();

        $purchasedCourses = $user->coursePurchases()
            ->whereRelation('course', 'is_published', true)
            ->with('course')
            ->latest()
            ->get()
            ->map(fn (CoursePurchase $purchase): array => $purchase->course->only(['title', 'slug']));

        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->withCount('lessons')
            ->get()
            ->filter(fn (Course $course) => $access->canAccessCourse($user, $course))
            ->values()
            ->map(fn (Course $course): array => [
                ...$course->only(['title', 'slug', 'thumbnail_url', 'lessons_count']),
                'completed_lessons_count' => (int) ($completedCounts[$course->id] ?? 0),
            ]);

        return Inertia::render('dashboard', [
            'subscription' => $subscription ? $this->subscriptionData($subscription) : null,
            // Untuk ajakan perpanjang kalau tidak ada langganan aktif
            'endedSubscription' => $subscription ? null : $this->lastEndedSubscription($user),
            'continueLearning' => $this->continueLearning($user, $progress, $completedCounts),
            'courses' => $courses,
            'purchasedCourses' => $purchasedCourses,
        ]);
    }

    /**
     * @return array{plan_name: string, ends_at: string}
     */
    private function subscriptionData(Subscription $subscription): array
    {
        return [
            'plan_name' => $subscription->plan->name,
            'ends_at' => $subscription->ends_at->toIso8601String(),
        ];
    }

    /**
     * @return array{plan_name: string, ends_at: string}|null
     */
    private function lastEndedSubscription(User $user): ?array
    {
        $ended = $user->subscriptions()
            ->where('ends_at', '<=', now())
            ->with('plan')
            ->latest('ends_at')
            ->first();

        return $ended ? $this->subscriptionData($ended) : null;
    }

    /**
     * @param  Collection<int, int>  $completedCounts
     * @return array<string, mixed>|null
     */
    private function continueLearning(User $user, CourseProgress $progress, Collection $completedCounts): ?array
    {
        $course = $progress->lastStudiedCourse($user);

        if (! $course) {
            return null;
        }

        return [
            ...$course->only(['title', 'slug', 'thumbnail_url']),
            'lessons_count' => $course->lessons()->count(),
            'completed_lessons_count' => (int) ($completedCounts[$course->id] ?? 0),
            'next_lesson_id' => $progress->resumeLessonId($user, $course, $user->completedLessonIds($course)),
        ];
    }
}
