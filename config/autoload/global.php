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

            # register the odmauth adapter as an oauth adapter with zf-mvc-auth
            'adapters' => [
                'odmauth' => [
                    'adapter' => 'OdmAuth\\Adapter\\OdmAdapter',
                    'storage' => [
                        'adapter' => 'mongo',
                        'database' => 'doctrine',
                        'dsn' => 'mongodb://localhost:27017',
                        'route' => '/oauth',
                    ],
                ],
            ],
        ],
    ],
];
