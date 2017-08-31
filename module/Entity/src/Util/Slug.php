<?php

namespace Entity\Util;


/**
 * Utility for creation of url-friendly slugs
 * Class Slug
 * @package Entity\Util
 */
class Slug
{
    /**
     * Turn a label into a slug
     * @see http://stackoverflow.com/questions/5305879/automatic-clean-and-seo-friendly-url-slugs
     * @param string $label
     * @param string $separator
     * @return string
     */
    public static function slugify($label, $separator = '-')
    {
        $accentsRegex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $specialCases = array('&' => 'and', "'" => '', '"' => '-');
        $label = mb_strtolower(trim($label), 'UTF-8');
        $label = str_replace(array_keys($specialCases), array_values($specialCases), $label);
        $label = preg_replace($accentsRegex, '$1', htmlentities($label, ENT_QUOTES, 'UTF-8'));
        $label = preg_replace("/[^a-z0-9]/u", "$separator", $label);
        $label = preg_replace("/[$separator]+/u", "$separator", $label);
        $label = trim($label,'-');

        return $label;
    }

    /**
     * Test if a slug (or tag) is valid.
     *
     * @param $slug
     *
     * @return bool
     */
    public static function isValid($slug)
    {
        return (!ctype_digit($slug) && preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $slug));
    }
}
