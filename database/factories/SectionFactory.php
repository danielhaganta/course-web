<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->randomElement([
                'Pengenalan',
                'Persiapan Project',
                'Teknik Dasar',
                'Teknik Lanjutan',
                'Studi Kasus',
                'Finishing dan Export',
            ]),
            'sort_order' => 0,
        ];
    }
}
