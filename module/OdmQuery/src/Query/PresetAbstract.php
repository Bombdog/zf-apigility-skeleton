<?php

namespace OdmQuery\Query;

/**
 * Abstract for creating preset queries.
 * @package OdmQuery\Query
 */
abstract class PresetAbstract
{
    /**
     * A name for the query (just the classname camelcased)
     * @return string
     */
    public function getName()
    {
        $fqName = get_class($this);

        return lcfirst(substr($fqName, strrpos($fqName, '\\') + 1));
    }

    /**
     * An array of terms to be used for filtering
     * @return array
     */
    public function getFilter()
    {
        return [];
    }

    /**
     * An an array of fields and ordering for each field
     * @return array
     */
    public function getSort()
    {
        return [];
    }

    /**
     * An array of fields to be used in a select
     * @return array
     */
    public function getFields()
    {
        return [];
    }
}
