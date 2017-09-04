<?php
namespace Fixture\V1\Rpc\Apply;

use Application\Api\Response\ApiSuccessResponse;
use Doctrine\ODM\MongoDB\DocumentManager;
use Entity\Document\User;
use Zend\Mvc\Controller\AbstractActionController;

class ApplyController extends AbstractActionController
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * Set dm
     * @param DocumentManager $dm
     */
    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }

    /**
     * Apply some fixtures
     * @return ApiSuccessResponse
     */
    public function applyAction()
    {
        $this->purge();

        # User "bob" with password "canread1"
        $user1 = new User();
        $user1->setEmail('bob@blah.com')
            ->setEmailVerified(true)
            ->setFirstName('Bob')
            ->setLastName('McTest')
            ->setEnabled(true)
            ->setScope('articles:read')
            ->setPassword('canread1');
        $this->dm->persist($user1);

        # User "donald" with password "canwrite1"
        $user2 = new User();
        $user2->setEmail('donald@blah.com')
            ->setEmailVerified(true)
            ->setFirstName('Donald')
            ->setLastName('McTest')
            ->setEnabled(true)
            ->setScope('articles:write_all')
            ->setPassword('canwrite1');
        $this->dm->persist($user2);
        $this->dm->flush();

        return new ApiSuccessResponse(204);
    }

    /**
     * Purge everything
     */
    private function purge()
    {
        # drop all mongo collections
        $this->dm->getSchemaManager()->dropCollections();
    }
}
