<?php

namespace Bot\Core\Api\OAuth\Scope;

use Bot\Core\Api\OAuth\Identity\IdentityContextInterface;

/**
 * A scope rule provides preset filters and fields for a scope.
 * Interface ScopeRuleInterface
 * @package Bot\Core\Api\OAuth\Scope\Filter
 * @see https://tradeintellect.atlassian.net/wiki/display/BD/OAuth2+Scopes
 */
interface ScopeRuleInterface
{
    /**
     * A figure that suggests how important this filter is if it is blended with other filters.
     * Use the numbers 0 (no priority), 80,90,100 etc for best results. As a rule scopes that apply to users with the
     * lowest privilage should have the highest priority.
     * @return int
     */
    public function getPriority();

    /**
     * The name of the scope being filtered.
     * Must return of the scope constants in the Scope class.
     */
    public function getScopeName();

    /**
     * An array that can be used as a filter when accessing the database.
     * @param IdentityContextInterface $id
     * @return array
     */
    public function getFilter(IdentityContextInterface $id);

    /**
     * The fields that are read-only in the current scope, or null if none.
     * NB read-only fields are used for updates. Don't apply read-only fields to x:read scopes
     * because x:read scopes are forbidden from making edits, so it would be a waste of time.
     * @return array
     */
    public function getReadonlyFields();

    /**
     * The blacklisted fields in this scope, or null if none.
     * @return array
     */
    public function getBlackListedFields();

    /**
     * The default fields in this scope, or null if none.
     * @return array
     */
    public function getDefaultFields();

}
