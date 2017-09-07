<?php

namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Adapter\OdmAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create and return API cache service.
 * @package Application\Api
 */
class OdmAdapterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return OdmAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = new OdmAdapter($container->get('OdmAuth\Service\OdmAuthService'), ['odmauth','oauth2']);

        if($container->has('object-cache')) {
            $adapter->setCache($container->get('object-cache'));
        }

        return $adapter;
    }
}
