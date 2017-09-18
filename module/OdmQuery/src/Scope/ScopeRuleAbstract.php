<?php

namespace OdmQuery\Scope;

/**
 * Base class for scope rules. Provides default values for priority and the field arrays.
 * To build a filter you must chose from the built in operators provided by the zend doctrine query builder
 * (but could be any other kind of querybuilder if desired). Choose from:
 *
 * 'eq, 'neq', 'lt', 'lte', 'gt', 'gte', 'isnull', 'isnotnull', 'in', 'notin', 'between', 'like' and 'regex'
 *
 * Example: only return invoices owned by the logged in user:
 *          [
 *              'field' => 'userId',
 *              'type'  => 'eq',
 *              'value' => 1234,
 *              'where' => 'and'
 *          ]
 *
 * This filter will be blended with any other filters that the user has requested, with this filter taking priority.
 * For security reasons filters from scope rules MUST always have priority over filters provided by the client side.
 * We need a good set of tests to enforce this and watch for regressions. Notice there is a priority field in our scope
 * filters, this is only for the situation where two or more scoped filters are merged.
 *
 */
abstract class ScopeRuleAbstract implements ScopeRuleInterface
{
    /**
     * A figure that suggests how important this filter is if it is blended with other filters.
     * Default priority is just zero (no priority)
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * The fields that are read-only in the current scope.
     * By default read-only fields are not set (null).
     * @return array
     */
    public function getReadonlyFields()
    {
        return null;
    }

    /**
     * The blacklisted fields in this scope, or null if none.
     * @note Blacklist fields are depended on default fields
     * @return array
     */
    public function getBlackListedFields()
    {
        return null;
    }

    /**
     * The default fields in this scope, or null if none.
     * @return array
     */
    public function getDefaultFields()
    {
        return null;
    }
}
