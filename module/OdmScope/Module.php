<?php

namespace OdmScope;

use OdmScope\Listener\PreDispatchListener;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

    /**
     * onBootstrap event to register additional event handlers for authorisation and logging.
     * @see https://akrabat.com/simple-logging-of-zf2-exceptions/
     * @see http://stackoverflow.com/questions/30720112/how-to-catch-and-log-all-exceptions-in-an-apigility-zf2-application
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        # pre dispatch handler to read target scopes
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, new PreDispatchListener(), 1002);
    }
}
