<?php
namespace BdAuth\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;
use ZF\MvcAuth\Factory\AuthenticationHttpAdapterFactory;

/**
 * Replacement "delegator factory" for class of the same name in ZF\MvcAuth\Factory
 * Class AuthenticationAdapterDelegatorFactory
 * @package Application\Auth\Factory
 */
class AuthenticationAdapterDelegatorFactory implements DelegatorFactoryInterface
{

    /**
     *
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

        foreach ($config['zf-mvc-auth']['authentication']['adapters'] as $type => $data) {

            if (! isset($data['adapter']) || ! is_string($data['adapter'])) {
                continue;
            }

            switch ($data['adapter']) {
                case 'ZF\MvcAuth\Authentication\HttpAdapter':
                    $adapter = AuthenticationHttpAdapterFactory::factory($type, $data, $services);
                    break;
                case 'ZF\MvcAuth\Authentication\OAuth2Adapter':
                case 'Application\Auth\OAuth2Adapter':
                case 'Auth\Adapter\OAuth2Adapter':
                    $adapter = AuthenticationOAuth2AdapterFactory::factory($type, $data, $services);
                    break;
                default:
                    $adapter = false;
                    break;
            }

            if ($adapter) {
                $listener->attach($adapter);
            }
        }

        return $listener;
    }
}
