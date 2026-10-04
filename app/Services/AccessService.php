<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;

// Satu-satunya tempat aturan boleh menonton materi
class AccessService
{
    public function canWatchLesson(?User $user, Lesson $lesson): bool
    {
        if ($lesson->is_preview) {
            return true;
        }

        // TODO: cek langganan aktif setelah sistem paket dibuat
        return $user?->is_admin === true;
    }
}
