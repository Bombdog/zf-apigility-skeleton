<?php
return [
    'service_manager' => [
        'factories' => [
            \Widgets\V1\Rest\Widget\WidgetResource::class => \Widgets\V1\Rest\Widget\WidgetResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'widgets.rest.widget' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/widget[/:widget_id]',
                    'defaults' => [
                        'controller' => 'Widgets\\V1\\Rest\\Widget\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'widgets.rest.widget',
        ],
    ],
    'zf-rest' => [
        'Widgets\\V1\\Rest\\Widget\\Controller' => [
            'listener' => \Widgets\V1\Rest\Widget\WidgetResource::class,
            'route_name' => 'widgets.rest.widget',
            'route_identifier_name' => 'widget_id',
            'collection_name' => 'widget',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Widgets\V1\Rest\Widget\WidgetEntity::class,
            'collection_class' => \Widgets\V1\Rest\Widget\WidgetCollection::class,
            'service_name' => 'Widget',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'Widgets\\V1\\Rest\\Widget\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'Widgets\\V1\\Rest\\Widget\\Controller' => [
                0 => 'application/vnd.widgets.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Widgets\\V1\\Rest\\Widget\\Controller' => [
                0 => 'application/vnd.widgets.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            \Widgets\V1\Rest\Widget\WidgetEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'widgets.rest.widget',
                'route_identifier_name' => 'widget_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
            \Widgets\V1\Rest\Widget\WidgetCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'widgets.rest.widget',
                'route_identifier_name' => 'widget_id',
                'is_collection' => true,
            ],
        ],
    ],
];
