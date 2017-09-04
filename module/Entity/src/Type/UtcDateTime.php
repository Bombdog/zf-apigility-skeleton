<?php
namespace Entity\Type;

use Doctrine\ODM\MongoDB\Types\Type;

/**
 * Class UtcType
 * This class ignores locale settings to return utc dates only.
 * @package Entity\Type
 */
class UtcDateTime extends Type
{
    /**
     * Convert DateTime to MongoDate.
     * NB if the value of the field is NULL the method convertToDatabaseValue() is not called.
     * ConvertToDatabaseValue() is used in a few places, such as UnitOfWork and PersistenceBuilder.
     * @param mixed $value
     * @return \MongoDate|null
     */
    public function convertToDatabaseValue($value)
    {
        if($value instanceof \DateTime) {
            if($value->getTimezone()->getName() == 'UTC') {
                return new \MongoDate($value->format('U'));
            }
            else {
                throw new \InvalidArgumentException("UtcDateTime strictly requires a UTC timezone");
            }
        }
        else {
            throw new \InvalidArgumentException("UtcDateTime expects a php DateTime object");
        }
    }

    /**
     * Convert MongoDate to UTC
     * ConvertToPHPValue() is used by ClassMetadataInfo, but only for identifiers.
     * @param mixed $value
     * @return \DateTime|null
     */
    public function convertToPHPValue($value)
    {
        $date = null;
        if ($value instanceof \MongoDate) {
            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone('UTC'));
            $date->setTimestamp($value->sec);
        }
        return $date;
    }

    /**
     * This is not used apparently. Documentation is sketchy.
     * @return string
     */
    public function closureToMongo()
    {
        return 'if ($value instanceof \DateTime && $value->getTimezone()->getName() == \'UTC\') { $return = new \MongoDate($value->getTimestamp()); } else { $return = null; }';
    }

    /**
     * closureToPHP() is used by the HydratorFactory class.
     * The code is written directly into the auto-generated hydrator for the entity.
     * @return string
     */
    public function closureToPHP()
    {
        return 'if ($value instanceof \MongoDate) { $return = new \DateTime(); $return->setTimezone(new \DateTimeZone(\'UTC\')); $return->setTimestamp($value->sec); } else { $return = null; }';
    }
}
