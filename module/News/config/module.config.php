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
                    'route' => '/news[/:article_id]',
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
                2 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Entity\\Document\\Article',
            'collection_class' => \News\V1\Rest\Article\ArticleCollection::class,
            'service_name' => 'Article',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'News\\V1\\Rest\\Article\\Controller' => 'Json',
        ],
        'accept_whitelist' => [
            'News\\V1\\Rest\\Article\\Controller' => [
                0 => 'application/vnd.news.v1+json',
                1 => 'application/json',
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
            'News\\V1\\Rest\\Article\\ArticleEntity' => [
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
            'Document\\Entity\\Article' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'news.rest.article',
                'route_identifier_name' => 'article_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
            '\\Entity\\Document\\Article' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'news.rest.article',
                'route_identifier_name' => 'article_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
            'Entity\\Document\\Article' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'news.rest.article',
                'route_identifier_name' => 'article_id',
                'hydrator' => \Zend\Hydrator\ArraySerializable::class,
            ],
        ],
    ],
    'zf-content-validation' => [
        'News\\V1\\Rest\\Article\\Controller' => [
            'input_filter' => 'News\\V1\\Rest\\Article\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'News\\V1\\Rest\\Article\\Validator' => [
            0 => [
                'name' => 'status',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            1 => [
                'name' => 'publishedAt',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            2 => [
                'name' => 'title',
                'required' => true,
                'filters' => [
                    0 => [
                        'name' => \Zend\Filter\StringTrim::class,
                    ],
                    1 => [
                        'name' => \Zend\Filter\StripTags::class,
                    ],
                ],
                'validators' => [],
            ],
            3 => [
                'name' => 'author',
                'required' => false,
                'filters' => [
                    0 => [
                        'name' => \Zend\Filter\StringTrim::class,
                    ],
                    1 => [
                        'name' => \Zend\Filter\StripTags::class,
                    ],
                ],
                'validators' => [],
            ],
            4 => [
                'name' => 'content',
                'required' => true,
                'filters' => [
                    0 => [
                        'name' => \Zend\Filter\StringTrim::class,
                    ],
                    1 => [
                        'name' => \Zend\Filter\StripTags::class,
                    ],
                ],
                'validators' => [],
            ],
            5 => [
                'name' => 'images',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            6 => [
                'name' => 'tags',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            7 => [
                'name' => 'createdAt',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            8 => [
                'name' => 'updatedAt',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            9 => [
                'name' => 'deletedAt',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
            10 => [
                'name' => 'sequence',
                'required' => false,
                'filters' => [],
                'validators' => [],
            ],
        ],
    ],
];
