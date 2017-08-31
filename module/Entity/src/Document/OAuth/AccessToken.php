<?php

namespace Entity\Document\Oauth;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Entity for the oauth_access_tokens collection.
 *
 * @ODM\Document(collection="oauth_access_tokens")
 * @ODM\Indexes({
 *   @ODM\Index(keys={"accessToken"="asc"},unique=true,name="idxAccessToken"),
 *   @ODM\Index(keys={"expires"="asc"},name="idxAccessExpires")
 * })
 */
class AccessToken
{
    /**
     * @ODM\Id
     * @var string
     */
    protected $id;

    /**
     * 32 character access token
     * @ODM\Field(type="string", name="access_token", nullable=true)
     * @var string
     */
    protected $accessToken;

    /**
     * Id of client eg "hapi-login" etc
     * @ODM\Field(type="string", name="client_id", nullable=true)
     * @var string
     */
    protected $clientId;

    /**
     * Integer timestamp expiry time
     * @ODM\Field(type="int", nullable=true)
     * @var int
     */
    protected $expires;

    /**
     * The user's id, not a sequence but the email that they use to log in
     * @ODM\Field(type="string", name="user_id", nullable=true)
     * @var string
     */
    protected $userId;

    /**
     * Oauth scopes (list of scopes seperated by spaces)
     * @ODM\Field(type="string", nullable=true)
     * @var string
     */
    protected $scope;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     * @return $this
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
}
