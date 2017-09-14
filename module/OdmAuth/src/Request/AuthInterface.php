<?php

namespace OdmAuth\Request;

/**
 * AuthInterface
 *
 * All of the typical fields in an Oauth grant request:
 *
 * e.g.
 * {
 *      grant_type=password (required)
 *      username=user@example.com (required)
 *      password=1234luggage (required)
 *      client_id=xxxxxxxxxx (optional)
 *      client_secret=xxxxxxxxxx (optional)
 *      scope=article:read (optional)
 * }
 *
 * @package OdmAuth\Request
 */
interface AuthInterface
{
    /**
     * Auth has the required fields
     *
     * @return bool
     */
    public function isValid();

    /**
     * Get the grant type, eg. "password"
     * @return null|string
     */
    public function getGrantType();

    /**
     * Get the user name
     *
     * @return null|string
     */
    public function getUsername();

    /**
     * @return null|string
     */
    public function getPassword();

    /**
     * @return null|string
     */
    public function getClientId();

    /**
     * @return null|string
     */
    public function getClientSecret();

    /**
     * @return null|string
     */
    public function getScope();
}