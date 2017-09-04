<?php
namespace Fixture\V1\Rpc\Apply;

use Application\Api\Response\ApiSuccessResponse;
use Entity\Document\User;
use Zend\Mvc\Controller\AbstractActionController;

class ApplyController extends AbstractActionController
{
    public function applyAction()
    {
        # User "bob" with password "canread1"
        $user = new User();
        $user->setEmail('bob@blah.com')
            ->setEmailVerified(true)
            ->setFirstName('Bob')
            ->setLastName('McTest')
            ->setEnabled(true)
            ->setScope('articles:read')
            ->setPassword('canread1');

        # User "donald" with password "canwrite1"
        $user = new User();
        $user->setEmail('donald@blah.com')
            ->setEmailVerified(true)
            ->setFirstName('Donald')
            ->setLastName('McTest')
            ->setEnabled(true)
            ->setScope('articles:write_all')
            ->setPassword('canwrite1');

        return new ApiSuccessResponse(204);
    }
}
