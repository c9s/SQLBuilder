<?php
namespace SQLBuilder;
use DateTime;
use Closure;


/**
 * XXX: this should be renamed to Deflator
 */
class Inflator
{
    public $driver;


    /**
     * For variable placeholder like PDO, we need 1 or 0 for boolean type,
     *
     * For pgsql and mysql sql statement, 
     * we use TRUE or FALSE for boolean type.
     *
     * FOr sqlite sql statement:
     * we use 1 or 0 for boolean type.
     */
    public function inflate($value)
    {
        if( $value instanceof Closure ) {
            $value = call_user_func($value);
        }

        if( $value === null ) {
            return 'NULL';
        }
        elseif( $value === true ) {
            if( $this->driver->type === 'sqlite' )
                return 1;
            return 'TRUE';
        }
        elseif( $value === false ) {
            if( $this->driver->type === 'sqlite' )
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
            if( $value instanceof DateTime ) {
                return $value->format(DateTime::ISO8601);
            }
        }
        elseif( is_array($value) ) { // raw value
            return $value[0];
        }
        return $value;
    }

    static function getInstance()
    {
        static $ins;
        return $ins ?: $ins = new self;
    }

}

