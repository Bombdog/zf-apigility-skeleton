<?php

namespace OdmAuth\Request;

/**
 * A customised request that adds identity etc to the request as it progresses
 * through the bootstrapping system. Extends zf-content negotiation for compatibility with
 * apigility.
 *
 */
class Request extends \ZF\ContentNegotiation\Request
{

    const STATUS_DENIED = 0;
    const STATUS_ANON = 1;
    const STATUS_IDENTIFIED = 2;
    const STATUS_IDENTIFIED_SECRET = 3;

    /**
     * Internal status flag
     * @var int
     */
    protected $status = self::STATUS_ANON;

    /**
     * Tracking header can be set in config
     * @var string
     */
    protected $trackingId;

    /**
     * Whether server-side caching is allowed.
     * Can be set to false to invalidate/bypass a caching behaviour
     * @var bool
     */
    protected $cacheAllow = true;

    /**
     * Object containing the oauth2 fields if any
     * @var Auth
     */
    protected $auth;

    /**
     * Target scope should be set before dispatch.
     * In our use case every route must have a scope to compare against.
     * See OdmScope module bootstrap.
     * @var
     */
    protected $targetScope;

    /**
     * Get the access token if provided. Only supports Bearer at the moment.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        $authHeader = $this->getHeader('Authorization');
        if ($authHeader !== false) {
            $tokenData = explode(' ', $authHeader->getFieldValue());
            # Only interested in Bearer tokens at the moment
            if ($tokenData[0] == 'Bearer' && isset($tokenData[1])) {
                return trim($tokenData[1]);
            }
        }

        return null;
    }

    /**
     * Scope - the requested scope. This can be set by the client when creating the token or sometimes when using
     * the token but modifying the scope, possibly for test purposes.
     *
     * @return null|string
     */
    public function getScope()
    {
        $auth = $this->getAuth();
        return $auth->getScope();
    }

    /**
     * Is server side caching of API results allowed?
     * @return boolean
     */
    public function isCacheAllowed()
    {
        return $this->cacheAllow;
    }

    /**
     * Enable or disable caching
     * @param boolean $caching
     * @return $this
     */
    public function setCacheAllow($caching)
    {
        $this->cacheAllow = (bool) $caching;
        return $this;
    }

    /**
     * Get tracking id.
     * Pseudo-session behaviour on an API, eg. for maintaining a history
     *
     * @return string
     */
    public function getTrackingId()
    {
        return $this->trackingId;
    }

    /**
     * Set the tracking id if found in the headers.
     *
     * @param $trackingId
     *
     * @return $this
     */
    public function setTrackingId($trackingId)
    {
        $this->trackingId = $trackingId;
        return $this;
    }

    /**
     * Get the details of an oauth request (lazy load)
     * @return Auth
     */
    public function getAuth()
    {
        if($this->auth === null) {
            $this->auth = new Auth($this->getContent());
        }

        return $this->auth;
    }

    /**
     *
     * @param mixed $targetScope
     *
     * @return Request
     */
    public function setTargetScope($targetScope)
    {
        $this->targetScope = $targetScope;
        return $this;
    }
}