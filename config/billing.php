<?php

return [

    'currency' => [
        'code'   => 'USD',
        'symbol' => '$',
    ],

    'plans' => [
        [
            'id'          => null,
            'name'        => 'free',
            'description' => 'Free plan',
            'active'      => true,
            'price'       => 0,
            'quota'       => [
                'posts' => 1,
            ],
        ],
        [
            'id'          => 'prod_XXXXXXXXXXX',
            'name'        => 'pro',
            'description' => 'Pro Plan',
            'active'      => true,
            'price'       => 1000,
            'quota'       => [
                'posts' => 3,
            ],
        ],
    ],
];
