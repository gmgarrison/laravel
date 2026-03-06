<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
        ]);

        User::factory()->afterCreating(function (User $user): void {
            $user->assignRole('editor');
        })->create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
        ]);
    }
}
