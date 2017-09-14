<?php

namespace OdmAuth\Request;

/**
 * The authorization body of a request
 * e.g.
 * params {
 *      grant_type=password (required)
 *      username=user@example.com (required)
 *      password=1234luggage (required)
 *      client_id=xxxxxxxxxx (optional)
 *      client_secret=xxxxxxxxxx (optional)
 *      scope=article:read (optional)
 * }
 */
class Auth implements AuthInterface
{
    /**
     *
     * @var array|false
     */
    protected $params;

    /**
     * Auth constructor.
     * Assumes and parses a json body.
     * If it's clearly too large then it doesn't bother.
     *
     * @param $body
     */
    public function __construct($body)
    {
        $len = mb_strlen($body);
        if($len && $len < 15000) {
            $this->params = json_decode($body,true);
        }
    }

    /**
     * Auth has the required fields
     *
     * @return bool
     */
    public function isValid()
    {
        return (
            $this->params !== false &&
            $this->getGrantType() !== null &&
            $this->getUsername() !== null &&
            $this->getPassword() !== null
        );
    }

    /**
     * Get the grant type, eg. "password"
     * @return null|string
     */
    public function getGrantType()
    {
        return $this->getParam('grant_type');
    }

    /**
     * Get the user name
     *
     * @return null|string
     */
    public function getUsername()
    {
        return $this->getParam('username');
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->getParam('password');
    }

    /**
     * @return null|string
     */
    public function getClientId()
    {
        return $this->getParam('client_id');
    }

    /**
     * @return null|string
     */
    public function getClientSecret()
    {
        return $this->getParam('client_secret');
    }

    /**
     * @return null|string
     */
    public function getScope()
    {
        return $this->getParam('scope');
    }

    /**
     * Get a parameter
     *
     * @param string $name
     *
     * @return string|null
     */
    private function getParam($name)
    {
        return (isset($this->params[$name])) ? $this->params[$name] : null;
    }
}