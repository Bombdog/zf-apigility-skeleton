<?php

namespace OdmQuery\Query;

/**
 * Class ScopeRestrictAbstract
 * @package OdmQuery\Query
 */
abstract class ScopeRestrictAbstract
{
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