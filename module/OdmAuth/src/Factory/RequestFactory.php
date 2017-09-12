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

        $request =  new HttpRequest();

        // @todo: make tracking and cache headers configurable
        $noCacheHeader  = 'X-Nocache';
        $trackingHeader = 'X-SessionId';

        # cache is always "allowed" (but needs to be written into resource of course)
        $request->setCacheAllow($request->getHeader($noCacheHeader) !== false);

        # if there is some tracking code (for anon user, with bearer token you don't need one)
        if($header = $request->getHeader($trackingHeader)) {
            $trackingId = $header->getFieldValue();
            $request->setTrackingId($trackingId);
        }

        return $request;
    }
}
