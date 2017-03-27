<?php

namespace SQLBuilder\Driver;

use DateTime;

/**
 * Class PgSQLDriver
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class PgSQLDriver extends BaseDriver
{
    /**
     * @param $id
     *
     * @return string
     */
    public function quoteIdentifier($id)
    {
        return '"' . addcslashes($id, '"') . '"';
    }

    /**
     * @param $value
     *
     * @return int|string
     */
    public function cast($value)
    {
        if ($value === true) {
            return 1;
        } elseif ($value === false) {
            return 0;
        }
        if ($value instanceof DateTime) {
            return $value->format(DateTime::ATOM);
        }

        return $value;
    }
}
