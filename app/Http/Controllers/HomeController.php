<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\DeviceSessions;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(DeviceSessions $devices): Response
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->withCount('lessons')
            ->withSum('lessons', 'duration_seconds')
            ->get(['id']);

        return Inertia::render('home', [
            // Untuk jawaban FAQ jumlah perangkat
            'maxDevices' => $devices->maxDevices(),
            // Ringkasan katalog untuk teks pembuka hero, angka asli bukan karangan
            'catalog' => [
                'courses' => $courses->count(),
                'lessons' => (int) $courses->sum('lessons_count'),
                'duration_seconds' => (int) $courses->sum('lessons_sum_duration_seconds'),
            ],
        ]);
    }
}
