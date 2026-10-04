<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->withCount('lessons')
            ->get()
            ->map(fn (Course $course): array => $course->only([
                'title', 'slug', 'summary', 'thumbnail_url', 'lessons_count',
            ]));

        return Inertia::render('courses/index', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): Response
    {
        abort_unless($course->is_published, 404);

        $course->load('sections.lessons');

        // Hanya kolom untuk tampilan. bunny_video_id, content, dan attachment tidak boleh terkirim.
        return Inertia::render('courses/show', [
            'course' => [
                ...$course->only(['title', 'summary', 'thumbnail_url']),
                // Deskripsi dari rich editor, disanitasi sebelum dirender sebagai HTML
                'description' => $course->description
                    ? RichContentRenderer::make($course->description)->toHtml()
                    : null,
                'sections' => $course->sections->map(fn (Section $section): array => [
                    ...$section->only(['id', 'title']),
                    'lessons' => $section->lessons->map(fn (Lesson $lesson): array => $lesson->only([
                        'id', 'title', 'type', 'duration_seconds', 'is_preview',
                    ])),
                ]),
            ],
        ]);
    }
}
