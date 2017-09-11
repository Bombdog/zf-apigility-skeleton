<?php

namespace OdmScope\Service;

use OdmScope\PrivilegeEscalationException;
use OdmScope\Scope\Scope;

/**
 * Utilities for parsing and matching scopes.
 */
class ScopeService
{
    /**
     * Merge a requested set of scopes with an allowed set of scopes.
     *
     * - The requested set can only be a reduced form of the allowed set
     * - Any attempt to escalate privilege throws an exception
     * - Any attempt to access another namespace throws an exception
     *
     * @param string $allowedScope
     * @param string $requestedScope
     *
     * @return string
     */
    public function mergeScopeRequest($allowedScope, $requestedScope)
    {
        # by requesting nothing then all of the allowed scopes are returned
        if (empty($requestedScope)) {
            return $allowedScope;
        }

        $allowedSet = $this->parseScopeSet($allowedScope);
        $requestedSet = $this->parseScopeSet($requestedScope);

        foreach ($requestedSet as $requested) {
            $this->validateScope($requested, $allowedSet);
        }

        return $requestedScope;
    }

    /**
     * Read in a space separated scope list and return an array of scope objects
     *
     * @param $scopeSet
     *
     * @return array
     */
    public function parseScopeSet($scopeSet)
    {
        $scopes = explode(' ', $scopeSet);
        $count = count($scopes);

        for ($i = 0; $i < $count; $i++) {
            $scopes[$i] = new Scope($scopes[$i]);
        }

        return $scopes;
    }

    /**
     * Test if a requested scope is valid, the
     *
     * @param Scope $requestedScope
     * @param $allowedSet
     */
    protected function validateScope(Scope $requestedScope, $allowedSet)
    {
        foreach ($allowedSet as $testScope) {
            if ($requestedScope->isTypeMatch($testScope)) {
                if ($requestedScope->isEscalationOf($testScope)) {
                    throw new PrivilegeEscalationException("Request for escalation $requestedScope is refused");
                }
                return;
            }
        }

        throw new PrivilegeEscalationException("Request for $requestedScope is refused");
    }
}