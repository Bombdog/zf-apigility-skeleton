<?php
return [
    'controllers' => [
        'factories' => [
            'Fixture\\V1\\Rpc\\Apply\\Controller' => \Fixture\V1\Rpc\Apply\ApplyControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'fixture.rpc.apply' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/fixtures/apply',
                    'defaults' => [
                        'controller' => 'Fixture\\V1\\Rpc\\Apply\\Controller',
                        'action' => 'apply',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'fixture.rpc.apply',
        ],
    ],
    'zf-rpc' => [
        'Fixture\\V1\\Rpc\\Apply\\Controller' => [
            'service_name' => 'apply',
            'http_methods' => [
                0 => 'GET',
            ],
            'route_name' => 'fixture.rpc.apply',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'Fixture\\V1\\Rpc\\Apply\\Controller' => 'Json',
        ],
        'accept_whitelist' => [
            'Fixture\\V1\\Rpc\\Apply\\Controller' => [
                0 => 'application/vnd.fixture.v1+json',
                1 => 'application/json',
                2 => 'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            'Fixture\\V1\\Rpc\\Apply\\Controller' => [
                0 => 'application/vnd.fixture.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-mvc-auth' => [
        'authorization' => [
            'Fixture\\V1\\Rpc\\Apply\\Controller' => [
                'actions' => [
                    'Apply' => [
                        'GET' => true,
                        'POST' => false,
                        'PUT' => false,
                        'PATCH' => false,
                        'DELETE' => false,
                    ],
                ],
            ],
        ],
    ],
];
