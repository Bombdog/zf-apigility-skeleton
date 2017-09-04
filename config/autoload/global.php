<?php
return [
    'zf-content-negotiation' => [
        'selectors' => [],
    ],
    'zf-mvc-auth' => [
        'authentication' => [
            'map' => [
                'News\\V1' => 'bdauth',
                'Fixture\\V1' => 'bdauth',
            ],
        ],
    ],
];
