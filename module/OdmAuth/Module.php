<?php

namespace OdmAuth;

/**
 * OdmAuth is a refactoring of ZF-OAuth2 with simpler factories for working with zf-auth
 * and pulling it all together in a single custom module connected to odm.
 *
 * I tried to put it in one place to see it and begin to simplify and strip out the stuff I don't need and add some
 * custom work with scopes and fields that I do need.
 */
class Module
{
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
