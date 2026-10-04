<?php

namespace App\Models;

use Database\Factories\LessonAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lesson_id
 * @property string $name
 * @property string $path
 * @property int $size_bytes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lesson_id', 'name', 'path', 'size_bytes'])]
class LessonAttachment extends Model
{
    /** @use HasFactory<LessonAttachmentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
