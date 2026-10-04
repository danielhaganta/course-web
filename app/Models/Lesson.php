<?php

namespace App\Models;

use App\Enums\LessonType;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $section_id
 * @property string $title
 * @property LessonType $type
 * @property string|null $bunny_video_id
 * @property int $duration_seconds
 * @property string|null $content
 * @property bool $is_preview
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['section_id', 'title', 'type', 'bunny_video_id', 'duration_seconds', 'content', 'is_preview', 'sort_order'])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return HasMany<LessonAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'is_preview' => 'boolean',
        ];
    }
}
