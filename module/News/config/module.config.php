<?php
return [
    'service_manager' => [
        'factories' => [
            \News\V1\Rest\Article\ArticleResource::class => \News\V1\Rest\Article\ArticleResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'news.rest.article' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/article[/:article_id]',
                    'defaults' => [
                        'controller' => 'News\\V1\\Rest\\Article\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'news.rest.article',
        ],
    ],
    'zf-rest' => [
        'News\\V1\\Rest\\Article\\Controller' => [
            'listener' => \News\V1\Rest\Article\ArticleResource::class,
            'route_name' => 'news.rest.article',
            'route_identifier_name' => 'article_id',
            'collection_name' => 'article',
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
            'entity_class' => \News\V1\Rest\Article\ArticleEntity::class,
            'collection_class' => \News\V1\Rest\Article\ArticleCollection::class,
            'service_name' => 'Article',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'News\\V1\\Rest\\Article\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'News\\V1\\Rest\\Article\\Controller' => [
                0 => 'application/vnd.news.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'News\\V1\\Rest\\Article\\Controller' => [
                0 => 'application/vnd.news.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            \News\V1\Rest\Article\ArticleEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'news.rest.article',
                'route_identifier_name' => 'article_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
            \News\V1\Rest\Article\ArticleCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'news.rest.article',
                'route_identifier_name' => 'article_id',
                'is_collection' => true,
            ],
        ],
    ],
];
