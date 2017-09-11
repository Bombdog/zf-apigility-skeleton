<?php

namespace BdScope;

return [
    'service_manager' => [
        'factories' => [
            'OdmScope\\Scope\\ScopeService' => 'OdmScope\\Factory\\ScopeServiceFactory',
       ],
    ],
];
