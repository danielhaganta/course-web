<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fn () => Order::generateCode(),
            'user_id' => User::factory(),
            'purchasable_type' => (new Plan)->getMorphClass(),
            'purchasable_id' => Plan::factory(),
            'amount' => fn (array $attributes) => Plan::query()->whereKey($attributes['purchasable_id'])->value('price'),
            'status' => OrderStatus::Pending,
            'snap_token' => fake()->uuid(),
            'expires_at' => now()->addDay(),
        ];
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes) => [
            'purchasable_type' => $course->getMorphClass(),
            'purchasable_id' => $course->id,
            'amount' => $course->price,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
