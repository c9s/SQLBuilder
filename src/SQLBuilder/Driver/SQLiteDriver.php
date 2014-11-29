<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Inflator;
use RuntimeException;

class SQLiteDriver extends BaseDriver
{

    public function quoteColumn($name)
    {
        if ($this->quoteColumn) {
            // return raw value if column name contains (non-word chars), eg: min( ), max( )
            if ( preg_match('/\W/',$name) ) {
                return $name;
            }
            return '`' . $name . '`';
        }
        return $name;
    }

    public function quoteTableName($name)
    {
        return $name;
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
    public function deflate($value)
    {
        if ($value instanceof Closure) {
            return call_user_func($value);
        }

        if ($value === NULL ) {
            return 'NULL';
        }
        elseif ($value === true ) {
            return 1;
        }
        elseif ($value === false ) {
            return 0;
        }
        elseif (is_integer($value) ) {
            return intval($value);
        }
        elseif (is_float($value) ) {
            return floatval($value);
        }
        elseif (is_string($value) ) {
            return $this->quote($value);
        }
        elseif (is_object($value)) {
            // convert DateTime object into string
            if ($value instanceof DateTime) {
                return $value->format(DateTime::ISO8601);
            } elseif (method_exists($value,'__toString')) {
                return $value->__toString();
            } else {
                throw new RuntimeException('Unsupported object type: ' . get_class($value));
            }
        }
        elseif (is_array($value) ) { // raw value
            return $value[0];
        }
        elseif ($value instanceof RawValue) {
            return $value[0]->__toString();
        }
        return $value;
    }

}

