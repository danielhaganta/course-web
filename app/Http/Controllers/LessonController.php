<?php

namespace App\Http\Controllers;

use App\Enums\LessonType;
use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Services\AccessService;
use App\Services\BunnyStream;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(Request $request, Course $course, Lesson $lesson, AccessService $access, BunnyStream $bunny): Response
    {
        abort_unless($course->is_published, 404);

        if (! $access->canWatch($request->user(), $lesson)) {
            return Inertia::render('lessons/locked', [
                'course' => $course->only(['title', 'slug']),
                'lesson' => $lesson->only(['title', 'is_exclusive']),
            ]);
        }

        $course->load('sections.lessons');

        // bunny_video_id tidak pernah dikirim, hanya URL embed bertoken
        return Inertia::render('lessons/show', [
            'course' => [
                ...$course->only(['title', 'slug']),
                'sections' => CourseSectionResource::collection($course->sections)->resolve(),
            ],
            'lesson' => [
                ...$lesson->only(['id', 'title', 'type', 'duration_seconds']),
                'content' => $lesson->type === LessonType::Text && $lesson->content
                    ? RichContentRenderer::make($lesson->content)->toHtml()
                    : null,
            ],
            'embedUrl' => $lesson->type === LessonType::Video && $lesson->bunny_video_id
                ? $bunny->embedUrl($lesson->bunny_video_id)
                : null,
            'watermark' => $request->user()?->email,
            // Path file di storage tidak pernah dikirim
            'attachments' => $lesson->attachments->map(fn (LessonAttachment $attachment): array => $attachment->only([
                'id', 'name', 'size_bytes',
            ])),
            'canDownload' => $access->canDownload($request->user(), $lesson),
            'completedLessonIds' => $request->user()?->completedLessonIds($course) ?? [],
        ]);
    }
}
