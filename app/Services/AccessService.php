<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Collection;

// Satu-satunya tempat aturan akses materi, download, dan course.
// Scoped: cache query langganan & pembelian hanya berlaku satu request.
#[Scoped]
class AccessService
{
    /** @var array<int, Collection<int, Plan>> */
    private array $activePlans = [];

    /** @var array<int, Collection<int, int>> */
    private array $purchasedCourseIds = [];

    public function canWatch(?User $user, Lesson $lesson): bool
    {
        if ($lesson->is_preview) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        // Pembeli satuan tidak dapat materi exclusive
        if ($lesson->is_exclusive) {
            return $this->activePlans($user)->contains('can_access_exclusive', true);
        }

        return $this->hasCourseAccess($user, $lesson->section->course_id);
    }

    public function canDownload(?User $user, Lesson $lesson): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->is_admin || $this->activePlans($user)->contains('can_download', true);
    }

    public function canAccessCourse(?User $user, Course $course): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->is_admin || $this->hasCourseAccess($user, $course->id);
    }

    private function hasCourseAccess(User $user, int $courseId): bool
    {
        return $this->activePlans($user)->isNotEmpty()
            || $this->purchasedCourseIds($user)->contains($courseId);
    }

    /**
     * Status plan tidak dicek: plan nonaktif tetap berlaku untuk langganan yang sudah ada.
     *
     * @return Collection<int, Plan>
     */
    private function activePlans(User $user): Collection
    {
        return $this->activePlans[$user->id] ??= $user->activeSubscriptions()->with('plan')->get()->pluck('plan');
    }

    /**
     * @return Collection<int, int>
     */
    private function purchasedCourseIds(User $user): Collection
    {
        return $this->purchasedCourseIds[$user->id] ??= $user->coursePurchases()->pluck('course_id');
    }
}
