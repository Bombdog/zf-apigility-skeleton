<?php
namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

/**
 * Replacement "delegator factory" for class of the same name in ZF\MvcAuth\Factory
 * Class AuthenticationAdapterDelegatorFactory
 * @package Application\Auth\Factory
 */
class AuthenticationAdapterDelegatorFactory implements DelegatorFactoryInterface
{

    /**
     *
     * @param ContainerInterface $services
     * @param string $name
     * @param callable $callback
     * @param array|null $options
     *
     * @return mixed
     */
    public function __invoke(
        ContainerInterface $services,
        $name,
        callable $callback,
        array $options = null
    ) {
        $listener = $callback();
        $config = $services->get('Config');

        if (! isset($config['zf-mvc-auth']['authentication']['adapters'])
            || ! is_array($config['zf-mvc-auth']['authentication']['adapters'])
        ) {
            return $listener;
        }

        $listener->attach($services->get('OdmAuth\Adapter\OdmAdapter'));

        return $listener;
    }
}
