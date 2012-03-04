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
 *  $driver->configure('quoter',array($pg,'escape'));
 *
 *  $driver->configure('quoter',array($pdo,'quote'));
 *  $driver->quoter = function($string) { 
 *      return your_escape_function( $string );
 *  };
 *
 */

class Driver
{


    /**
     * driver type
     *
     * @var string mysql, pgsql, sqlite
     */
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
     * string quoter handler
     *  
     *  Array:
     *
     *    array($obj,'method')
     */
    public $quoter;

    public $escaper;

    public $vars = array();


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

    public function setPlaceHolderVar($key,$value)
    {
        if( $this->placeholder && $this->placeholder === 'named' ) {
            $this->vars[ ':' . $key  ] = $value;
        }
        else {
            $this->vars[] = $value;
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
            // return raw value if column name contains (non-word chars), eg: min( ), max( )
            if( preg_match('/\W/',$name) )
                return $name;
            if( is_string($c) )
                return $c . $name . $c;
            if( $this->type == 'pgsql' )
                return '"' . $name . '"';
            elseif ( $this->type == 'mysql' )
                return '`' . $name . '`';
            elseif ( $this->type == 'sqlite' ) 
                return '`' . $name . '`';
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

            if( $this->type == 'pgsql' )
                return '"' . $name . '"';
            elseif ($this->type == 'mysql') 
                return '`' . $name . '`';
        }
        return $name;
    }

    /**
     * quote & escape string with single quote 
     */
    public function quote($string)
    {
        /**
         * quote:
         *
         *    string mysqli_real_escape_string ( mysqli $link , string $escapestr )
         *    string pg_escape_string ([ resource $connection ], string $data )
         *    string PDO::quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
         *
         *  $driver->configure('quote',array($pgconn,'escape_string'));
         */
        if( $this->quoter )
            return call_user_func( $this->quoter , $string );

        if( $this->escaper )
            return '\'' . call_user_func( $this->escaper , $string ) . '\'';
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

