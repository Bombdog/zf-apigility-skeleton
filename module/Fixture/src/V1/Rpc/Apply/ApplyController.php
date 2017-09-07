<?php
namespace Fixture\V1\Rpc\Apply;

use Application\Api\Response\ApiSuccessResponse;
use Doctrine\ODM\MongoDB\DocumentManager;
use Entity\Document\Oauth\Client;
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

        # Minimal client entry
        $client = new Client();
        $client->setClientId('bd-login');
        $this->dm->persist($client);

        # User "bob" with password "canread1"
        $user1 = new User();
        $user1->setUsername('bob@blah.com')
            ->setEmailVerified(true)
            ->setFirstName('Bob')
            ->setLastName('McTest')
            ->setEnabled(true)
            ->setScope('articles:read')
            ->setPassword('canread1');
        $this->dm->persist($user1);

        # User "donald" with password "canwrite1"
        $user2 = new User();
        $user2->setUsername('donald@blah.com')
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
