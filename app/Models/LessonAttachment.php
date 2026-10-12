<?php

namespace App\Models;

use Database\Factories\LessonAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public static function diskName(): string
    {
        return Config::string('filesystems.attachments.disk');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(self::diskName());
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Nama tampil + ekstensi file asli kalau belum ada.
     * Garis miring diganti karena tidak boleh ada di header Content-Disposition.
     */
    public function downloadName(): string
    {
        $name = str_replace(['/', '\\'], '-', $this->name);
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        if ($extension === '' || Str::endsWith(Str::lower($name), '.'.Str::lower($extension))) {
            return $name;
        }

        return "{$name}.{$extension}";
    }

    protected static function booted(): void
    {
        // File fisik ikut terhapus saat record dihapus atau filenya diganti
        static::deleted(fn (self $attachment) => self::disk()->delete($attachment->path));

        static::updated(function (self $attachment): void {
            if ($attachment->wasChanged('path')) {
                self::disk()->delete($attachment->getOriginal('path'));
            }
        });
    }
}
