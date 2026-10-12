<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;

// Hitungan progres belajar yang dipakai katalog, detail course, dan dashboard
class CourseProgress
{
    public function __construct(private AccessService $access) {}

    /**
     * Jumlah materi selesai per course, satu query untuk semua course.
     *
     * @return Collection<int, int>
     */
    public function completedCountsByCourse(User $user): Collection
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

    /**
     * Materi pertama yang bisa ditonton dan belum selesai. Kalau semua sudah selesai, kembali ke materi pertama.
     *
     * @param  Collection<int, int>  $completedLessonIds
     */
    public function resumeLessonId(?User $user, Course $course, Collection $completedLessonIds): ?int
    {
        $watchableIds = $course->loadMissing('sections.lessons')->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->filter(fn (Lesson $lesson) => $this->access->canWatch($user, $lesson))
            ->pluck('id');

        return $watchableIds->first(fn (int $id) => ! $completedLessonIds->contains($id))
            ?? $watchableIds->first();
    }

    /**
     * Course dari aktivitas progres terakhir (menandai atau membatalkan selesai) yang masih bisa diakses.
     */
    public function lastStudiedCourse(User $user): ?Course
    {
        $course = LessonProgress::query()
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->with('lesson.section.course')
            ->first()
            ?->lesson->section->course;

        return $course && $course->is_published && $this->access->canAccessCourse($user, $course)
            ? $course
            : null;
    }
}
