<?php
namespace OdmQuery\Query;

/**
 * Preset filters for use with APIs
 * Preset names should be valid php class names only.
 * @package OdmQuery\Query
 */
final class PresetFactory
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
    public static function getInstance($name)
    {
        $className = __NAMESPACE__ . '\Presets' . "\\" . ucfirst($name);
        if (class_exists($className)) {
            return new $className();
        }

        return false;
    }
}
