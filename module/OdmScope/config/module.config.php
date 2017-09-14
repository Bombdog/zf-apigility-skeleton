<?php

namespace BdScope;

return [
    'service_manager' => [
        'factories' => [
            'OdmScope\\Service\\ScopeService' => 'OdmScope\\Factory\\ScopeServiceFactory',
       ],
    ],
];
