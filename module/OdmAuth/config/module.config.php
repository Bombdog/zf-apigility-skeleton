<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 *
 *
 * This module is a re-worked version of zfcampus/ZF-OAuth2
 */
namespace OdmAuth;

return [

    # divert any authentication requests to our odm adapter via the delegator
    'service_manager' => array(
        'delegators' => array(
            'ZF\MvcAuth\Authentication\DefaultAuthenticationListener' => array(
                'OdmAuth\Factory\AuthenticationAdapterDelegatorFactory',
            ),
        ),
        'factories' => array(
            'Request'                      => 'OdmAuth\Factory\RequestFactory',
            'OdmAuth\\Adapter\\OdmAdapter' => 'OdmAuth\\Factory\\OdmAdapterFactory',
            'OdmAuth\\Service\\OdmAuthService' => 'OdmAuth\\Factory\\OdmAuthServiceFactory'
            //'OdmAuth\Adapter\MongoAdapter'  => 'OdmAuth\Factory\MongoAdapterFactory',
            //'OdmAuth\Provider\UserId\AuthenticationService' => 'OdmAuth\Provider\UserId\AuthenticationServiceFactory',
            //'OdmAuth\Service\OAuth2Server'  => 'OdmAuth\Factory\NamedOAuth2ServerFactory'
        ),
    ),

    # register the odmauth adapter
    'zf-mvc-auth' => [
        'authentication' => [
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

    'controllers' => [
        'factories' => [
            'OdmAuth\Controller\Auth' => 'OdmAuth\Factory\AuthControllerFactory',
        ],
    ],

    'router' => [
        'routes' => [
            'oauth' => [
                'type' => 'literal',
                'options' => [
                    'route'    => '/oauth',
                    'defaults' => [
                        'controller' => 'OdmAuth\Controller\Auth',
                        'action'     => 'token',
                    ],
                ],
                /*
                'may_terminate' => true,
                'child_routes' => [
                    'revoke' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/revoke',
                            'defaults' => [
                                'action' => 'revoke',
                            ],
                        ],
                    ],
                    'authorize' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/authorize',
                            'defaults' => [
                                'action' => 'authorize',
                            ],
                        ],
                    ],
                    'resource' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/resource',
                            'defaults' => [
                                'action' => 'resource',
                            ],
                        ],
                    ],
                    'code' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/receivecode',
                            'defaults' => [
                                'action' => 'receiveCode',
                            ],
                        ],
                    ],
                ],
                */
            ],
        ],
    ],



    'view_manager' => [
        'template_map' => [
            'oauth/authorize'    => __DIR__ . '/../view/zf/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/zf/auth/receive-code.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'zf-oauth2' => [
        /*
         * Config can include:
         * - 'storage' => 'name of storage service' - typically OdmAuth\Adapter\PdoAdapter
         * - 'db' => [ // database configuration for the above PdoAdapter
         *       'dsn'      => 'PDO DSN',
         *       'username' => 'username',
         *       'password' => 'password'
         *   ]
         * - 'storage_settings' => [ // configuration to pass to the storage adapter
         *       // see https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/Pdo.php#L57-L66
         *   ]
         */
        'grant_types' => [
            'client_credentials' => true,
            'authorization_code' => true,
            'password'           => true,
            'refresh_token'      => true,
            'jwt'                => true,
        ],

        'storage' => 'OdmAuth\Adapter\MongoAdapter',

        # we use a custom name for our users collection... users :-)
        'storage_settings' => [
            'user_table' => 'users'
        ],

        /*
         * Error reporting style
         *
         * If true, client errors are returned using the
         * application/problem+json content type,
         * otherwise in the format described in the oauth2 specification
         * (default: true)
         */
        'api_problem_error_response' => true,
        'allow_implicit' => false, // default (set to true when you need to support browser-based or mobile apps)
        'access_lifetime' => 43200,
        'enforce_state'  => true,  // default
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'OdmAuth\Controller\Auth' => [
                'ZF\ContentNegotiation\JsonModel' => [
                    'application/json',
                    'application/*+json',
                ],
                'Zend\View\Model\ViewModel' => [
                    'text/html',
                    'application/xhtml+xml',
                ],
            ],
        ],
    ],
];
