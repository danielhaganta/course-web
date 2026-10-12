<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->createTestUser('admin', isAdmin: true);
        // Member biasa sengaja tanpa subscription
        $this->createTestUser('member', isAdmin: false);

        $this->call([PlanSeeder::class, CourseSeeder::class]);
    }

    private function createTestUser(string $role, bool $isAdmin): void
    {
        $account = Config::array("seeder.{$role}");

        foreach ($account as $field => $value) {
            if (blank($value)) {
                throw new RuntimeException(Str::upper("{$role}_{$field}").' belum diisi di .env');
            }
        }

        User::factory()->create([
            ...$account,
            'email_verified_at' => now(),
            'is_admin' => $isAdmin,
        ]);
    }
}
