<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create a demo/sample user only in development or when explicitly enabled.
        // This avoids creating unwanted users during normal application runtime.
        if (app()->environment('local') || env('APP_DEMO', false)) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            SecuritySettingSeeder::class,
        ]);
    }
}
