<?php
namespace Entity\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for redis connections.
 * Class RedisFactory
 * @package Entity\Service\Factory
 */
class RedisFactory implements FactoryInterface
{
    /**
     * Create a redis connection.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return \Redis
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $redis = new \Redis();
        if(isset($config['redis']['conn'])) {
            $redis->connect($config['redis']['conn']);
        }
        else {
            $redis->connect('127.0.0.1');
        }

        return $redis;
    }
}
