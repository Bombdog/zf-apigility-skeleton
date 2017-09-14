<?php

namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Service\OdmAuthService;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create odm auth half-baked oauth2 service
 * @package Application\Api
 */
class OdmAuthServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return OdmAuthService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = new OdmAuthService($container->get('doctrine.documentmanager.odm_default'));

        if($container->has('OdmScope\Service\ScopeService')) {
            $service->setScopeService($container->get('OdmScope\Service\ScopeService'));
        }

        return $service;
    }
}
