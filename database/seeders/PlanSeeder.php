<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Harga contoh, diubah lewat admin
        $plans = [
            ['name' => 'Standard Monthly', 'price' => 149_000, 'duration_days' => 30, 'is_premium' => false],
            ['name' => 'Standard Yearly', 'price' => 1_490_000, 'duration_days' => 365, 'is_premium' => false],
            ['name' => 'Premium Monthly', 'price' => 249_000, 'duration_days' => 30, 'is_premium' => true],
            ['name' => 'Premium Yearly', 'price' => 1_500_000, 'duration_days' => 365, 'is_premium' => true],
        ];

        foreach ($plans as $index => $plan) {
            Plan::factory()->create([
                'name' => $plan['name'],
                'slug' => Str::slug($plan['name']),
                'price' => $plan['price'],
                'duration_days' => $plan['duration_days'],
                'can_access_exclusive' => $plan['is_premium'],
                'can_download' => $plan['is_premium'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
