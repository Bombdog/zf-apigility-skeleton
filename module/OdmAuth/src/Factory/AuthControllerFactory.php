<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use OdmAuth\Controller\AuthController;

class AuthControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authController = new AuthController(
            $container->get('OdmAuth\Service\OdmAuthService')
        );

        return $authController;
    }
}
