<?php
namespace Fixture\V1\Rpc\Apply;

use Zend\ServiceManager\ServiceManager;

class ApplyControllerFactory
{
    public function __invoke(ServiceManager $controllers)
    {
        $dm = $controllers->get('doctrine.documentmanager.odm_default');
        $controller =  new ApplyController();
        $controller->setDocumentManager($dm);

        return $controller;
    }
}
