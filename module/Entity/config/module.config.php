<?php
namespace Entity;

return array(
	'doctrine' => array(
		'driver' => array(
	        'odm_driver' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Document'
                ],
            ),
            /*
            * MUST register the Entities with doctrine odm_driver or else you will get an Exception
            * referring to "Demo\Entity\Entry cannot be found in the chained namespaces"
            */
	        'odm_default' => array(
                'drivers' => array(
                    'Entity\Document' => 'odm_driver'
                ),
            ),
        ),
    ),
);
