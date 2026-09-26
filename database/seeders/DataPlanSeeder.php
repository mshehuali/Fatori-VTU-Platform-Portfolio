<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataPlan;

class DataPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            // --- MTN SME DATA ---
            ['network' => 'MTN', 'name' => '500MB SME', 'plan_id' => '500', 'cost_price' => 125, 'price' => 150],
            ['network' => 'MTN', 'name' => '1GB SME',   'plan_id' => '1000', 'cost_price' => 250, 'price' => 300],
            ['network' => 'MTN', 'name' => '2GB SME',   'plan_id' => '2000', 'cost_price' => 500, 'price' => 600],
            ['network' => 'MTN', 'name' => '3GB SME',   'plan_id' => '3000', 'cost_price' => 750, 'price' => 900],
            ['network' => 'MTN', 'name' => '5GB SME',   'plan_id' => '5000', 'cost_price' => 1250, 'price' => 1500],
            ['network' => 'MTN', 'name' => '10GB SME',  'plan_id' => '10000', 'cost_price' => 2500, 'price' => 3000],

            // --- AIRTEL CG DATA ---
            ['network' => 'AIRTEL', 'name' => '500MB CG', 'plan_id' => 'AIR500', 'cost_price' => 130, 'price' => 160],
            ['network' => 'AIRTEL', 'name' => '1GB CG',   'plan_id' => 'AIR1000', 'cost_price' => 260, 'price' => 320],
            ['network' => 'AIRTEL', 'name' => '2GB CG',   'plan_id' => 'AIR2000', 'cost_price' => 520, 'price' => 640],
            ['network' => 'AIRTEL', 'name' => '5GB CG',   'plan_id' => 'AIR5000', 'cost_price' => 1300, 'price' => 1600],

            // --- GLO CG DATA ---
            ['network' => 'GLO', 'name' => '1GB CG',   'plan_id' => 'GLO1000', 'cost_price' => 240, 'price' => 300],
            ['network' => 'GLO', 'name' => '2GB CG',   'plan_id' => 'GLO2000', 'cost_price' => 480, 'price' => 600],
            ['network' => 'GLO', 'name' => '3GB CG',   'plan_id' => 'GLO3000', 'cost_price' => 720, 'price' => 900],
            ['network' => 'GLO', 'name' => '5GB CG',   'plan_id' => 'GLO5000', 'cost_price' => 1200, 'price' => 1500],

            // --- 9MOBILE SME DATA ---
            ['network' => '9MOBILE', 'name' => '1GB SME', 'plan_id' => '9MOB1000', 'cost_price' => 230, 'price' => 300],
            ['network' => '9MOBILE', 'name' => '2GB SME', 'plan_id' => '9MOB2000', 'cost_price' => 460, 'price' => 600],
        ];

        foreach ($plans as $plan) {
            // updateOrCreate prevents duplicates if you run this command twice
            DataPlan::updateOrCreate(
                ['network' => $plan['network'], 'name' => $plan['name']], 
                array_merge($plan, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}