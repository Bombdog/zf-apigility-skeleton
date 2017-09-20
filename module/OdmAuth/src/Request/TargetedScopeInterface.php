<?php

namespace OdmAuth\Request;

use OdmScope\Scope\ScopeSet;

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
     * @return $this
     */
    public function setWriteAllScope(array $writeAllScope);

    /**
     * Return only the scope that is targeted by a specific http verb
     *
     * @param string $httpMethod
     *
     * @return ScopeSet
     */
    public function getTargetScopeSetForHttpMethod($httpMethod);
}
