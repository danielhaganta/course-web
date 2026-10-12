<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoursePurchase>
 */
class CoursePurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory()->published(),
        ];
    }
}
