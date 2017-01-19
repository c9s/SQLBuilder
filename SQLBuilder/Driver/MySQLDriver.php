<?php

namespace SQLBuilder\Driver;

use SQLBuilder\ArgumentArray;
use DateTime;

class MySQLDriver extends BaseDriver
{
    public $quoteColumn = false;
    public $quoteTable = false;

    public function quoteIdentifier($id)
    {
        return '`'.addcslashes($id, '`').'`';
    }

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

    public function deflate($value, ArgumentArray $args = null)
    {
        if ($value instanceof DateTime) {
            // MySQL does not support date time string with timezone
            return $this->quote($value->format('Y-m-d H:i:s'));
        }

        return parent::deflate($value, $args);
    }
}
