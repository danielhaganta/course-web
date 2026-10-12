<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
use App\Services\AccessService;
use App\Services\CourseProgress;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request, AccessService $access, CourseProgress $progress): Response
    {
        $user = $request->user();
        $completedCounts = $user ? $progress->completedCountsByCourse($user) : collect();

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

    public function show(Request $request, Course $course, AccessService $access, CourseProgress $progress): Response
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
                'resume_lesson_id' => $canAccess ? $progress->resumeLessonId($request->user(), $course, $completedLessonIds) : null,
            ],
        ]);
    }
}
