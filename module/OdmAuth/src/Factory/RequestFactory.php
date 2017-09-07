<?php

namespace OdmAuth\Factory;

use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\Console\Request as ConsoleRequest;
use OdmAuth\Request\Request as HttpRequest;

class RequestFactory
{
    /**
     * @param  ContainerInterface $container
     * @return ConsoleRequest|HttpRequest
     */
    public function __invoke(ContainerInterface $container)
    {
        if (Console::isConsole()) {
            return new ConsoleRequest();
        }

        return new HttpRequest();
    }
}
