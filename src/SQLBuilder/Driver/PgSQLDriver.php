<?php
namespace SQLBuilder\Driver;
use DateTime;
use Exception;
use RuntimeException;

class PgSQLDriver extends BaseDriver
{
    public function quoteIdentifier($id) {
        return '"' . addcslashes($id,'"') . '"';
    }

    public function cast($value)
    {
        if ($value === true) {
            return 1;
        } else if ($value === false) {
            return 0;
        }
        return $value;
    }
}

