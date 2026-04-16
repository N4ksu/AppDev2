<?php

namespace Database\Seeders;

use App\Models\SecuritySetting;
use Illuminate\Database\Seeder;

class SecuritySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SecuritySetting::updateOrCreate(
            ['id' => 1],
            [
                'max_failed_attempts' => 3,
                'lock_duration_minutes' => 15,
            ]
        );
    }
}
