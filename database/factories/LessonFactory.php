<?php

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => fake()->randomElement([
                'Mengenal Antarmuka Aplikasi',
                'Mengatur Project dan Timeline',
                'Import dan Mengelola Footage',
                'Memotong dan Menyusun Klip',
                'Koreksi Warna Dasar',
                'Membaca Scope dan Histogram',
                'Membuat Look Sinematik',
                'Transisi yang Halus',
                'Menyesuaikan Audio',
                'Export untuk YouTube dan Instagram',
            ]),
            'type' => LessonType::Video,
            // Bunny Stream video ID berformat GUID
            'bunny_video_id' => fake()->uuid(),
            'duration_seconds' => fake()->numberBetween(3 * 60, 40 * 60),
            'content' => null,
            'is_preview' => false,
            'sort_order' => 0,
        ];
    }
}
