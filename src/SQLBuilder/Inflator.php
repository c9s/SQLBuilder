<?php
namespace SQLBuilder;


class Inflator
{
    public $driver;

    function inflate($value)
    {
        if( $value === null ) {
            return 'NULL';
        }
        elseif( $value === true ) {
            return 'TRUE';
        }
        elseif( $value === false ) {
            return 'FALSE';
        }
        elseif( is_integer($value) ) {
            return (int) $value;
        }
        elseif( is_float($value) ) {
            return (float) $value;
        }
        elseif( is_string($value) ) {
            return '\'' . call_user_func( $this->driver->escaper , $value ) . '\'';
        }
        elseif( is_object($value) ) {
            // convert DateTime object into string
            if( is_a($value,'DateTime') ) {
                return $value->format(\DateTime::ISO8601);
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

