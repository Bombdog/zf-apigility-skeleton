<?php

namespace OdmQuery;

return [
    'service_manager' => [
        'factories' => [
            'OdmQuery\\Query\\ApiQuery' => 'OdmQuery\\Factory\\ApiQueryFactory',
            'QueryManager' => 'OdmQuery\\Factory\\ApiQueryManagerFactory',
       ],
    ],
];
