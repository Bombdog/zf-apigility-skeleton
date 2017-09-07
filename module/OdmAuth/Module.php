<?php

namespace OdmAuth;

use Zend\Mvc\MvcEvent;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener;
use ZF\MvcAuth\MvcAuthEvent;

/**
 * BdAuth is a refactoring of ZF-OAuth2 with simpler factories for working with zf-auth
 * and pulling it all together in a single custom module connected to odm.
 *
 * I tried to put it in one place to see it and begin to simplify and strip out the stuff I don't need and add some
 * custom work with scopes and fields that I do need.
 */
class Module
{
    /**
     * Manually attach the odm adapter.
     * This is a workaround for the factory which requires a rather arcane configuration for oauth2.
     * The built-in listener factory "DefaultAuthenticationListenerFactory::createOAuth2Server()" requires
     * specific configuration and the dependency cannot be easily injected, so we decided to bypass.
     *
     * @see zfcampus/zf-mvc-auth/src/Factory/DefaultAuthenticationListenerFactory.php
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();
        $listener = new DefaultAuthenticationListener();
        $listener->attach($sm->get('OdmAuth\\Adapter\\OdmAdapter'));
        $eventManager->attach(MvcAuthEvent::EVENT_AUTHENTICATION,
                $listener,
                1003
            );
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
