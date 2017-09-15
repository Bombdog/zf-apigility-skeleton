<?php

namespace OdmQuery\Query;

/**
 * Class ScopeRuleFactory
 * @package OdmQuery\Query
 */
class ScopeRuleFactory
{
    private function __construct()
    {
    }

    /**
     * Get a preset query. Returns false if the preset does not exist.
     * Preset name can be camelcased.
     * @param $name
     * @return PresetAbstract|bool
     */
    public static function getInstance(array $scopes)
    {







        $className = __NAMESPACE__ . '\ScopeRule' . "\\" . ucfirst($name);
        if (class_exists($className)) {
            return new $className();
        }

        return false;
    }




}