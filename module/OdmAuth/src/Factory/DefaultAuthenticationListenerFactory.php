<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Adapter\OdmAdapter;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener;

/**
 * Factory for creating the DefaultAuthenticationListener from configuration.
 */
class DefaultAuthenticationListenerFactory extends \ZF\MvcAuth\Factory\DefaultAuthenticationListenerFactory
{
    /**
     * Create and return a DefaultAuthenticationListener.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     * @return DefaultAuthenticationListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = new DefaultAuthenticationListener();

        $httpAdapter = $this->retrieveHttpAdapter($container);
        if ($httpAdapter) {
            $listener->attach($httpAdapter);
        }

        $oauth2Server = $this->createOAuth2Server($container);
        if ($oauth2Server) {
            $listener->attach($oauth2Server);
        }

        $authenticationTypes = $this->getAuthenticationTypes($container);
        if ($authenticationTypes) {
            $listener->addAuthenticationTypes($authenticationTypes);
        }

        $listener->setAuthMap($this->getAuthenticationMap($container));

        return $listener;
    }

    /**
     * Create an OAuth2 server by introspecting the config service
     *
     * @param ContainerInterface $container
     * @return false|OdmAdapter
     */
    protected function createOAuth2Server(ContainerInterface $container)
    {
        if($container->has('OdmAuth\\Adapter\\OdmAdapter')) {
            return $container->get('OdmAuth\\Adapter\\OdmAdapter');
        }

        return false;
    }
}
