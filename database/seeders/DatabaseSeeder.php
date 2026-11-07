<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $users = User::factory()->count(9)->create();
        $users->push($testUser);

        foreach ($users as $user) {
            Post::factory()
                ->count(fake()->numberBetween(5, 20))
                ->for($user)
                ->create();
        }
    }
}
