<?php
namespace SQLBuilder;


/*
 *  $driver->configure('driver','pgsql');
 *
 *  trim spaces
 *
 *  $driver->configure('trim',true);
 *
 *  $driver->configure('placeholder','named');
 *
 *  $driver->configure('escaper',array($pg,'escape'));
 *
 *  $driver->configure('escaper',array($pdo,'quote'));
 */

class Driver
{

    public $type;

    /**
     * should we quote table name in SQL ?
     */
    public $quoteTable = false;


    /**
     * should we quote column name in SQL ?
     */
    public $quoteColumn = false;


    /**
     * should we trim space ?
     */
    public $trim = false;


    /**
     * get place holder
     */
    public $placeholder = false;


    /**
     * string escaper handler
     *  
     *  Array:
     *
     *    array($obj,'method')
     */
    public $escaper;



    static function create()
    {
        return new static;
    }

    static function getInstance()
    {
        static $self;
        return $self ? $self : $self = new static;
    }

    public function __construct($driverType = null)
    {
        $this->type = $driverType;
        $this->escaper = 'addslashes';
        $this->inflator = new Inflator;
        $this->inflator->driver = $this;
    }


    /**
     * configure options
     *
     *
     *
     */
    public function configure($key,$value)
    {
        switch( $key ) {
        case 'trim':
            $this->trim = $value;
            break;

            /* named or true */
        case 'placeholder':
            $this->placeholder = $value;
            break;

        case 'quote_table':
            $this->quoteTable = $value;
            break;

        case 'quote_column':
            $this->quoteColumn = $value;
            break;


        /** 
         * valid driver:
         *
         *   pgsql, mysql, sqlite
         */
        case 'driver':
            $this->type = $value;
            if( $this->type == 'mysql' ) {
                $this->quoteColumn = false;
                $this->quoteTable = false;
            }
            break;

            /**
             * sql style:
             *    PDO or mysqli ... etc
             */
        case 'style':
            $this->style = $value;
            break;

        }
    }



    /**
     * get place holder string,
     * the returned value is depends on driver.
     *
     * for named parameter, this returns a key with a ":" char.
     * for question-mark parameter, this always returns a "?" char.
     *
     * @param string $key column name
     *
     * @return string
     */
    public function getPlaceHolder($key)
    {
        if( $this->placeholder && $this->placeholder === 'named' ) {
            return ':' . $key;
        }
        else {
            return '?';
        }
    }

    /**
     * check driver option to quote column name
     *
     * column quote can be configured by 'quote_column' option.
     *
     * @param string $name column name
     * @return string column name with/without quotes.
     */
    public function getQuoteColumn($name)
    {

        if( $c = $this->quoteColumn ) {
            if( preg_match('/\W/',$name) )
                return $name;
            if( is_string($c) )
                return $c . $name . $c;
            return '"' . $name . '"';
        }
        return $name;
    }


    /**
     * check driver optino to quote table name
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     * @return string table name with/without quotes.
     */
    public function getQuoteTableName($name) 
    {
        if( $c = $this->quoteTable ) {
            if( is_string($c) ) 
                return $c . $name . $c;
            return '"' . $name . '"';
        }
        return $name;
    }

    /**
     * escape string with single quote 
     */
    public function escape($value)
    {
        /**
         * escaper:
         *
         *    string mysqli_real_escape_string ( mysqli $link , string $escapestr )
         *    string pg_escape_string ([ resource $connection ], string $data )
         *    string PDO::quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
         *
         *  $driver->configure('escaper',array($pgconn,'escape_string'));
         */
        return '\'' . call_user_func( $this->escaper , $value ) . '\'';
    }

    /**
     * inflate value to SQL statement
     *
     * for example, boolean types should be translate to string TRUE or FALSE.
     */
    public function inflate($value)
    {
        return $this->inflator->inflate($value);
    }


    /**
     * Convert a normal associative array to 
     * named parameter array.
     *
     * @param array $args
     * @return array
     */
    public function convertToNamedParameters($args)
    {
        $new = array();
        foreach( $args as $k => $v ) {
            $new[ ':'. $k ] = $v;
        }
        return $new;
    }

}

