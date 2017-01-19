<?php

namespace SQLBuilder\Driver;

use DateTime;

class PgSQLDriver extends BaseDriver
{
    public function quoteIdentifier($id)
    {
        return '"'.addcslashes($id, '"').'"';
    }

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
