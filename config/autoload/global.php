<?php
return [
    'redis' => [
        'conn' => 'localhost',
    ],
    'zf-content-negotiation' => [
        'selectors' => [],
    ],
    'zf-mvc-auth' => [
        'authentication' => [
            'map' => [
                'News\\V1' => 'odmauth',
                'Fixture\\V1' => 'odmauth',
            ],
        ],
    ],
];
