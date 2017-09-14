<?php
namespace Entity\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Entity\Util\Decimal;


/**
 * Currency type for storing decimals as strings. Mongo has no decimal format.
 * NB. Doesn't sort numerically
 * @see http://stackoverflow.com/questions/11541939/mongodb-what-about-decimal-type-of-value
 * @package Entity\Type
 */
class Currency extends Type
{
    /**
     * Database value.
     * @param mixed $value
     * @return null|string
     */
    public function convertToDatabaseValue($value)
    {
        return $value !== null ? Decimal::toCurrency($value) : null;
    }

    public function convertToPHPValue($value)
    {
        return $value !== null ? (string) $value : null;
    }

    public function closureToMongo()
    {
        return '$return = (string) $value;';
    }

    public function closureToPHP()
    {
        return '$return = (string) $value;';
    }
}
