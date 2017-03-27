<?php

namespace SQLBuilder\Driver;

use DateTime;
use SQLBuilder\ArgumentArray;

/**
 * Class SQLiteDriver
 *
 * Currently not supporting this SQLiteDriver.
 *
 * @deprecated
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class SQLiteDriver extends BaseDriver
{
    /**
     * @param $id
     *
     * @return string
     */
    public function quoteIdentifier($id)
    {
        return '`' . addcslashes($id, '`') . '`';
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

    /**
     * For variable placeholder like PDO, we need 1 or 0 for boolean type,.
     *
     * For pgsql and mysql sql statement,
     * we use TRUE or FALSE for boolean type.
     *
     * FOr sqlite sql statement:
     * we use 1 or 0 for boolean type.
     *
     * @param                                $value
     * @param \SQLBuilder\ArgumentArray|null $args
     *
     * @param bool                           $quote
     *
     * @return int|mixed|string
     */
    public function deflate($value, ArgumentArray $args = null, $quote = true)
    {
        // Special cases for SQLite
        if ($value === true) {
            return 1;
        } elseif ($value === false) {
            return 0;
        } else {
            return parent::deflate($value, $args);
        }

        return $value;
    }
}
