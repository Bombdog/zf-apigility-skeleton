<?php
namespace Entity\Util;

/**
 * Class UTC
 * Simple utilities for working with UTC. All of our stored dates should
 * be UTC to avoid possible problems with timezones.
 */
class UTC
{
    /**
     * Constant for an ISO 8601 with fractional seconds
     */
    const ISO8601U = 'Y-m-d\TH:i:s.uO';

    /**
     * @var \DateTime
     */
    private static $utcTz;

    /**
     * Create a date that is Universal Time Zone.
     *
     * @param string $time
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function createUTCDate($time = 'now')
    {
        if (null == self::$utcTz) {
            self::$utcTz = new \DateTimeZone('UTC');
        }

        return new \DateTime($time, self::$utcTz);
    }

    /**
     * Create a date that is Universal Time Zone.
     *
     * @param int $timestamp
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function dateFromTimestamp($timestamp)
    {
        if (null == self::$utcTz) {
            self::$utcTz = new \DateTimeZone('UTC');
        }

        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(self::$utcTz);

        return $date;
    }

    /**
     * Create a UTC datetime from the commonly used ISO8601
     *
     * eg. 2017-02-14T11:10:33Z
     * or the fractional 2017-02-14T11:10:33.050Z
     *
     * @param $time
     * @param bool $fractionalSecs
     *
     * @return \DateTime
     */
    public static function dateFromIso8601Time($time, $fractionalSecs = false)
    {
        if (null == self::$utcTz) {
            self::$utcTz = new \DateTimeZone('UTC');
        }

        if ($fractionalSecs) {
            $format = self::ISO8601U;
        } else {
            $format = \DateTime::ISO8601;
        }

        return \DateTime::createFromFormat($format, $time, self::$utcTz);
    }

    /**
     * Check that a date is in UTC timezone
     *
     * @param \DateTime $dateTime
     *
     * @return bool
     */
    public static function isUtcDate(\DateTime $dateTime)
    {
        $tz = $dateTime->getTimezone();

        return ('UTC' == $tz->getName());
    }
}
