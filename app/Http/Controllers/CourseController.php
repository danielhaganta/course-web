<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
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

        return Inertia::render('courses/show', [
            'course' => [
                ...$course->only(['title', 'slug', 'summary', 'thumbnail_url']),
                // Deskripsi dari rich editor, disanitasi sebelum dirender sebagai HTML
                'description' => $course->description
                    ? RichContentRenderer::make($course->description)->toHtml()
                    : null,
                'sections' => CourseSectionResource::collection($course->sections)->resolve(),
            ],
        ]);
    }
}
