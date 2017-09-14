<?php

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Utils being autoloaded by composer.json
 */

if (!function_exists('dump')) {
    /**
     * Dump a value with elegance.
     *
     * @credit Laravel package (LARAVEL ROCKS!)
     *
     * @param  mixed $value
     * @return void
     */
    function dump($value)
    {
        if (class_exists(CliDumper::class)) {
            $dumper = 'cli' === PHP_SAPI ? new CliDumper : new HtmlDumper;
            $dumper->dump((new VarCloner)->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @credit Laravel package (LARAVEL ROCKS!)
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(function ($x) {
            dump($x);
        }, func_get_args());

        die(1);
    }
}
