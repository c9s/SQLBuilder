<?php

namespace SQLBuilder\Driver;

use DateTime;
use SQLBuilder\ArgumentArray;

/**
 * Class MySQLDriver
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class MySQLDriver extends BaseDriver
{
    public $quoteTable = false;

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
        if (is_bool($value)) {
            return intval($value);
        }
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    /**
     * @param                                $value
     * @param \SQLBuilder\ArgumentArray|null $args
     *
     * @return mixed|string
     */
    public function deflate($value, ArgumentArray $args = null)
    {
        if ($value instanceof DateTime) {
            // MySQL does not support date time string with timezone
            return $this->quote($value->format('Y-m-d H:i:s'));
        }

        return parent::deflate($value, $args);
    }
}
