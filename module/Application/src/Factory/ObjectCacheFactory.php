<?php
namespace Application\Factory;

use Application\Cache\ObjectCache;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create and return API cache service.
 * @package Application\Api
 */
class ObjectCacheFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ObjectCache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \Redis $redis */
        $redis = $container->get('redis');

        return new ObjectCache($redis);
    }
}
