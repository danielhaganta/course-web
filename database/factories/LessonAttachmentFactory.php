<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LessonAttachment>
 */
class LessonAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'name' => fake()->randomElement([
                'File Project.zip',
                'Footage Latihan.zip',
                'LUT Pack.zip',
                'Preset Color Grading.zip',
            ]),
            'path' => 'attachments/'.Str::uuid().'.zip',
            'size_bytes' => fake()->numberBetween(1_000_000, 500_000_000),
        ];
    }
}
