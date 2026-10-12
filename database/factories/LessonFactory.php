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
                'Getting to Know the Interface',
                'Setting Up the Project and Timeline',
                'Importing and Organizing Footage',
                'Cutting and Arranging Clips',
                'Basic Color Correction',
                'Reading Scopes and Histograms',
                'Building a Cinematic Look',
                'Smooth Transitions',
                'Adjusting Audio',
                'Exporting for YouTube and Instagram',
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
