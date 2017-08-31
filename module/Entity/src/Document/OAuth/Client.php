<?php

namespace Entity\Document\Oauth;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Entity for oauth_clients collection.
 *
 * @ODM\Document(collection="oauth_clients")
 * @author Andy H
 */
class Client
{
    /**
     * @ODM\Id
     * @var string
     */
    protected $id;

    /**
     * @ODM\Field(type="string", name="client_id", nullable=true)
     * @var string
     */
    protected $clientId;

    /**
     * @ODM\Field(type="string", name="client_secret", nullable=true)
     * @var string
     */
    protected $clientSecret;

    /**
     * @ODM\Field(type="string", name="redirect_uri", nullable=true)
     * @var string
     */
    protected $redirectUri;

    /**
     * @ODM\Field(type="string", name="grant_types", nullable=true)
     * @var string
     */
    protected $grantTypes;

    /**
     * @ODM\Field(type="string", nullable=true)
     * @var string
     */
    protected $scope;

    /**
     * @ODM\Field(type="string", name="user_id", nullable=true)
     * @var int
     */
    protected $userId;

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

}