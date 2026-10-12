<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\Section;
use App\Services\AccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bagian + daftar materi untuk halaman publik.
 * Jangan tambahkan bunny_video_id, content, atau attachment di sini.
 *
 * @mixin Section
 */
class CourseSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $access = app(AccessService::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'lessons' => $this->lessons->map(fn (Lesson $lesson): array => [
                ...$lesson->only(['id', 'title', 'type', 'duration_seconds', 'is_preview', 'is_exclusive']),
                'can_watch' => $access->canWatch($request->user(), $lesson),
            ]),
        ];
    }
}
