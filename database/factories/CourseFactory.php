<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Cinematic Color Grading for Beginners',
                'Video Editing Techniques for Content Creators',
                'DaVinci Resolve from Scratch',
                'Storytelling Through Editing',
                'Motion Graphics Basics',
                'Audio Mixing for Video',
            ]),
            // Suffix acak supaya slug tetap unik walau judul sama
            'slug' => fn (array $attributes) => Str::slug($attributes['title']).'-'.Str::lower(Str::random(6)),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'thumbnail_path' => null,
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
