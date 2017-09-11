<?php

namespace OdmScope\Factory;

use Interop\Container\ContainerInterface;
use OdmScope\Service\ScopeService;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for the scope service
 */
class ScopeServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ScopeService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScopeService();
    }
}
