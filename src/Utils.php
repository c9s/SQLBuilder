<?php

namespace SQLBuilder;

/**
 * Class Utils
 *
 * @package SQLBuilder
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class Utils
{

    /**
     * @param $a
     *
     * @return \SQLBuilder\Raw|ToSqlInterface
     */
    public static function buildExprArg($a)
    {
        if ($a instanceof ToSqlInterface) {
            return $a;
        }

        return new Raw($a);
    }

    /**
     * @param $args
     *
     * @return array
     */
    public static function buildFunctionArguments($args)
    {
        return array_map([Utils::class, 'buildExprArg'], $args);
    }
}
