<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * -------------------------------------------------
         * Default application settings
         * -------------------------------------------------
         */
        DB::table('settings')->insertOrIgnore([
            [
                'key'   => 'app_theme',
                'value' => 'light',
            ],
        ]);

        /**
         * -------------------------------------------------
         * Default test/admin user
         * -------------------------------------------------
         */
        User::factory()->create([
            'name'   => 'Test User',
            'email'  => 'test@example.com',
            'role'   => 'admin',
            'status' => 'active',
        ]);
    }
}
