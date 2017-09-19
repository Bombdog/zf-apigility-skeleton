<?php
namespace Entity;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Types\Type;
use Entity\Subscriber\SequenceSubscriber;
use Entity\Subscriber\TimestampSubscriber;
use Zend\Mvc\MvcEvent;

/**
 * Entity module - module to manage our entity model
 * @package Entity
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get Autoloader config.
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src',
                ),
            ),
        );
    }

    /**
     * onBootstrap (MVC startup event)
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        /** @var DocumentManager $dm */
        $dm = $sm->get('doctrine.documentmanager.odm_default');

        /** @var EventManager $evtManager */
        $evtManager = $dm->getEventManager();
        $evtManager->addEventSubscriber(new SequenceSubscriber());
        $evtManager->addEventSubscriber(new TimestampSubscriber());

        if (!Type::hasType('utcdatetime')) {
            Type::addType('utcdatetime', 'Entity\Type\UtcDateTime');
        }

        if (!Type::hasType('currency')) {
            Type::addType('currency', 'Entity\Type\Currency');
        }
    }
}
