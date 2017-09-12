<?php

namespace OdmAuth\Request;

/**
 * Targeted scopes.
 * These are passed into the configuration.
 *
 * Interface TargetedScopeInterface
 * @package OdmAuth\Request
 */
interface TargetedScopeInterface
{
    /**
     * Set the name of the route (for reference)
     *
     * @param $routeName
     *
     * @return $this
     */
    public function setRouteName($routeName);

    /**
     * Set a read scope or scopes that are allowed to read from the resource
     *
     * @param array $readScope
     *
     * @return $this
     */
    public function setReadScope(array $readScope);

    /**
     * Set a write scope or scopes that are allowed to write to the resource
     *
     * @param array $writeScope
     *
     * @return $this
     */
    public function setWriteScope(array $writeScope);

    /**
     * Set a write_all scope or scopes that are allowed to write to the resource (all articles)
     *
     * @param array $writeAllScope
     *
     * @return mixed
     */
    public function setWriteAllScope(array $writeAllScope);

    /**
     * Does an identified user have the right to access the targeted scope
     *
     * @param $httpMethod
     * @param $identityScope
     *
     * @return mixed
     */
    public function isAccessAllowed($httpMethod, $identityScope);
}
