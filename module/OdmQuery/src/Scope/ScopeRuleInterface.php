<?php

namespace OdmQuery\Scope;

/**
 * A scope rule provides preset filters and fields for a scope.
 * Interface ScopeRuleInterface
 */
interface ScopeRuleInterface
{
    /**
     * A figure that suggests how important this rule is if it is blended with other rules.
     * Use the numbers 0 (no priority), 80,90,100 etc for best results. Scopes that apply to users with the
     * lowest privilege (more restrictive) should have the highest priority.
     * @return int
     */
    public function getPriority();

    /**
     * An array that can be used as a filter when accessing the database.
     *
     * @return array
     */
    public function getFilter();

    /**
     * The fields that are read-only in the current scope, or null if none.
     * NB read-only fields are used for updates. Don't apply read-only fields to x:read scopes
     * because x:read scopes should be forbidden from making edits, so it would be a waste of time.
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
