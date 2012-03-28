<?php
namespace SQLBuilder;
use DateTime;

class Inflator
{
    public $driver;

    function inflate($value)
    {
        /**
         * For variable placeholder like PDO, we need 1 or 0 for boolean type,
         * for pgsql and sqlite sql statement, 
         * we can use TRUE or FALSE for boolean type.
         */
        if( $value === null ) {
            return 'NULL';
        }
        elseif( $value === true ) {
            if( $this->driver->placeholder )
                return 1;
            return 'TRUE';
        }
        elseif( $value === false ) {
            if( $this->driver->placeholder )
                return 0;
            return 'FALSE';
        }
        elseif( is_integer($value) ) {
            return (int) $value;
        }
        elseif( is_float($value) ) {
            return (float) $value;
        }
        elseif( is_string($value) ) {
            return $this->driver->quote($value);
        }
        elseif( is_object($value) ) {
            // convert DateTime object into string
            if( is_a($value,'DateTime') ) {
                return $value->format(DateTime::ISO8601);
            }
        }
        return $value;
    }

    static function getInstance()
    {
        static $ins;
        return $ins ?: $ins = new self;
    }

}

