<?php
namespace SQLBuilder\Driver;
use SQLBuilder\ArgumentArray;
use DateTime;
use Exception;
use RuntimeException;

/**
 * Currently not supporting this SQLiteDriver
 *
 * @codeCoverageIgnore
 */
class SQLiteDriver extends BaseDriver
{
    public function quoteIdentifier($id) {
        return '`' . addcslashes($id,'`') . '`';
    }

    public function cast($value)
    {
        if ($value === true) {
            return 1;
        } else if ($value === false) {
            return 0;
        }
        if ($value instanceof DateTime) {
            return $value->format(DateTime::ATOM);
        }
        return $value;
    }

    /**
     * For variable placeholder like PDO, we need 1 or 0 for boolean type,
     *
     * For pgsql and mysql sql statement, 
     * we use TRUE or FALSE for boolean type.
     *
     * FOr sqlite sql statement:
     * we use 1 or 0 for boolean type.
     */
    public function deflate($value, ArgumentArray $args = NULL)
    {
        // Special cases for SQLite
        if ($value === true) {
            return 1;
        } elseif ($value === false ) {
            return 0;
        } else {
            return parent::deflate($value, $args);
        }
        return $value;
    }
}

