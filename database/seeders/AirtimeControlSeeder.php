<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AirtimeControl;

class AirtimeControlSeeder extends Seeder
{
    public function run()
    {
        $networks = [
            ['network' => 'MTN',     'discount_percent' => 1.00], // 1% Off
            ['network' => 'AIRTEL',  'discount_percent' => 1.00],
            ['network' => 'GLO',     'discount_percent' => 2.00], // 2% Off
            ['network' => '9MOBILE', 'discount_percent' => 2.00],
        ];

        foreach ($networks as $net) {
            AirtimeControl::updateOrCreate(
                ['network' => $net['network']],
                $net
            );
        }
    }
}