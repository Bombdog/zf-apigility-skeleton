<?php

namespace Entity;

return [
    'doctrine' => [
        'driver' => [
            'odm_driver' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Document'
                ],
            ],
            /*
            * MUST register the Entities with doctrine odm_driver or else you will get an Exception
            * referring to "Demo\Entity\Entry cannot be found in the chained namespaces"
            */
            'odm_default' => [
                'drivers' => [
                    'Entity\Document' => 'odm_driver'
                ],
            ],
        ],
    ],
];
