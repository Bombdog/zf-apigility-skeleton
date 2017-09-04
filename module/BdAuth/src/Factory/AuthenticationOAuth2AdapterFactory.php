<?php

namespace BdAuth\Factory;

use BdAuth\Adapter\OAuth2Adapter;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Copied directly from the class of the same name in the zf-mvc-auth module.
 * Class AuthenticationOAuth2AdapterFactory
 * @package Application\Auth\Factory
 */
final class AuthenticationOAuth2AdapterFactory
{
    /**
     * Intentionally empty and private to prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * Create and return an OAuth2Adapter instance.
     * 
     * @param string|array $type 
     * @param array $config 
     * @param ServiceLocatorInterface $services 
     * @return OAuth2Adapter
     * @throws ServiceNotCreatedException when missing details necessary to
     *     create instance and/or dependencies.
     */
    public static function factory($type, array $config, ServiceLocatorInterface $services)
    {
        if (! isset($config['storage']) || ! is_array($config['storage'])) {
            throw new ServiceNotCreatedException('Missing storage details for OAuth2 server');
        }

        $adapter = new OAuth2Adapter(
            OAuth2ServerFactory::factory($config['storage'], $services),
            $type
        );

        if($services->has('object-cache')) {
            $cache = $services->get('object-cache');
            $adapter->setCache($cache);
        }

        return $adapter;
    }
}
