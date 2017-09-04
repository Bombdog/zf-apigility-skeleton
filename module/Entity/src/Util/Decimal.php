<?php

namespace Entity\Util;

use Entity\EntityInvalidArgumentException;

/**
 * Utilities for working with string decimals
 * @package Entity\Util
 */
class Decimal
{
    /**
     * Convert any number to a (positive) decimal string value with arbitrary decimal places
     *
     * @param mixed $value
     * @param int
     * @return string
     */
    public static function toDecimal($value, $trimPlaces = 0)
    {
        # Filters
        # best effort for numeric types - always casting to string.
        if (is_float($value) || is_int($value)) {
            $value = number_format($value, 2, '.', '');
        } else if (ctype_digit($value)) {
            $value .= '.00';
        }

        # Remove leading zeros / add trailing zero
        if (is_string($value)) {
            $value = ltrim($value, '0');
            if (strpos($value, '.') === 0) {
                $value = '0' . $value;
            }
        }

        # Validate and return
        $parts = explode('.', $value);
        if (count($parts) == 2 && ctype_digit($parts[0]) && ctype_digit($parts[1])) {
            if (strlen($parts[1]) == 1) {
                $parts[1] .= '0';
            }

            if ($trimPlaces && $trimPlaces > 0) {
                $parts[1] = substr($parts[1], 0, $trimPlaces);
            }

            return implode('.', $parts);
        }

        # Otherwise throw an exception
        throw new EntityInvalidArgumentException("Failed to convert value to decimal value with two decimal places.");
    }

    /**
     * Convert to currency (decimal string) value.
     * Alias of toDecimal
     * @param mixed $value
     * @return string
     */
    public static function toCurrency($value)
    {
        $value = self::toDecimal($value, 2);
        return $value;
    }

    /**
     * Convert to a percentage (decimal string) value.
     * Alias of toDecimal
     * @param $value
     * @return string
     */
    public static function toPercentage($value)
    {
        $value = self::toDecimal($value);
        return $value;
    }
}
