<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\AccessService;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request, AccessService $access): Response
    {
        $user = $request->user();
        $completedCounts = $user ? $this->completedCountsByCourse($user) : collect();

        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->withCount('lessons')
            ->withSum('lessons', 'duration_seconds')
            ->get()
            ->map(fn (Course $course): array => [
                ...$course->only(['title', 'slug', 'summary', 'thumbnail_url', 'lessons_count']),
                'duration_seconds' => (int) $course->getAttribute('lessons_sum_duration_seconds'),
                // Null kalau user belum login atau tidak punya akses ke course ini
                'completed_lessons_count' => $user && $access->canAccessCourse($user, $course)
                    ? (int) ($completedCounts[$course->id] ?? 0)
                    : null,
            ]);

        return Inertia::render('courses/index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course, AccessService $access): Response
    {
        abort_unless($course->is_published, 404);

        $course->load('sections.lessons');

        $sections = CourseSectionResource::collection($course->sections)->resolve();
        $canAccess = $access->canAccessCourse($request->user(), $course);
        $completedLessonIds = $request->user()?->completedLessonIds($course) ?? collect();

        return Inertia::render('courses/show', [
            'course' => [
                ...$course->only(['title', 'slug', 'summary', 'thumbnail_url', 'price']),
                // Deskripsi dari rich editor, disanitasi sebelum dirender sebagai HTML
                'description' => $course->description
                    ? RichContentRenderer::make($course->description)->toHtml()
                    : null,
                'sections' => $sections,
                'can_access' => $canAccess,
                'completed_lesson_ids' => $completedLessonIds,
                'resume_lesson_id' => $canAccess ? $this->resumeLessonId($sections, $completedLessonIds) : null,
            ],
        ]);
    }

    /**
     * Materi pertama yang bisa ditonton dan belum selesai. Kalau semua sudah selesai, kembali ke materi pertama.
     *
     * @param  array<int, array{lessons: Collection<int, array{id: int, can_watch: bool}>}>  $sections
     * @param  Collection<int, int>  $completedLessonIds
     */
    private function resumeLessonId(array $sections, Collection $completedLessonIds): ?int
    {
        $watchableIds = collect($sections)
            ->flatMap(fn (array $section) => $section['lessons'])
            ->where('can_watch', true)
            ->pluck('id');

        return $watchableIds->first(fn (int $id) => ! $completedLessonIds->contains($id))
            ?? $watchableIds->first();
    }

    /**
     * Jumlah materi selesai per course, satu query untuk seluruh katalog.
     *
     * @return Collection<int, int>
     */
    private function completedCountsByCourse(User $user): Collection
    {
        return LessonProgress::query()
            ->completed()
            ->where('lesson_progress.user_id', $user->id)
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('sections', 'sections.id', '=', 'lessons.section_id')
            ->groupBy('sections.course_id')
            ->selectRaw('sections.course_id, count(*) as total')
            ->pluck('total', 'sections.course_id')
            ->map(fn (mixed $total): int => (int) $total);
    }
}
