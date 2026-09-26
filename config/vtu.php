<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Data Plans Configuration
    |--------------------------------------------------------------------------
    |
    | You can later move this to the database. For now, config is enough.
    |
    */

    'data_plans' => [
        // MTN
        [
            'network'  => 'MTN',
            'code'     => 'mtn_500mb_1d',
            'name'     => '500MB',
            'validity' => '1 Day',
            'amount'   => 200,
        ],
        [
            'network'  => 'MTN',
            'code'     => 'mtn_1gb_7d',
            'name'     => '1GB',
            'validity' => '7 Days',
            'amount'   => 500,
        ],
        [
            'network'  => 'MTN',
            'code'     => 'mtn_3gb_30d',
            'name'     => '3GB',
            'validity' => '30 Days',
            'amount'   => 1500,
        ],

        // Airtel
        [
            'network'  => 'Airtel',
            'code'     => 'airtel_750mb_7d',
            'name'     => '750MB',
            'validity' => '7 Days',
            'amount'   => 300,
        ],
        [
            'network'  => 'Airtel',
            'code'     => 'airtel_1_5gb_30d',
            'name'     => '1.5GB',
            'validity' => '30 Days',
            'amount'   => 1000,
        ],

        // Glo
        [
            'network'  => 'Glo',
            'code'     => 'glo_1gb_5d',
            'name'     => '1GB',
            'validity' => '5 Days',
            'amount'   => 400,
        ],
        [
            'network'  => 'Glo',
            'code'     => 'glo_2_5gb_30d',
            'name'     => '2.5GB',
            'validity' => '30 Days',
            'amount'   => 1200,
        ],

        // 9mobile
        [
            'network'  => '9mobile',
            'code'     => '9mobile_500mb_3d',
            'name'     => '500MB',
            'validity' => '3 Days',
            'amount'   => 250,
        ],
        [
            'network'  => '9mobile',
            'code'     => '9mobile_1_5gb_30d',
            'name'     => '1.5GB',
            'validity' => '30 Days',
            'amount'   => 1100,
        ],
    ],

];
