<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Inflator;

abstract class BaseDriver
{
    const NO_PARAM_MARKER = 0;

    /**
     * Question mark parameter marker
     *
     * (?,?)
     */
    const QMARK_PARAM_MARKER = 1;


    /**
     * Named parameter marker
     */
    const NAMED_PARAM_MARKER = 2;



    /**
     * @var boolean Should we trim space ?
     */
    public $trim = false;


    public $paramMarker = self::NAMED_PARAM_MARKER;


    public $quoteColumn;

    public $quoteTable;


    /**
     * String quoter handler
     *  
     *  Array:
     *
     *    array($obj,'method')
     */
    public $quoter;

    static function create()
    {
        return new static;
    }

    static function getInstance()
    {
        static $self;
        return $self ? $self : $self = new static;
    }

    public function __construct()
    {
        $this->inflator = new Inflator($this);
    }


    public function setQuoter(callable $quoter) {
        $this->quoter = $quoter;
    }


    /**
     * @param boolean $enable 
     */
    public function setQuoteTable($enable = true) { 
        $this->quoteTable = $enable;
    }

    /**
     * @param boolean $enable 
     */
    public function setQuoteColumn($enable = true) {
        $this->quoteColumn = $enable;
    }

    public function setTrim($enable = true) {
        $this->trim = $enable;
    }

    // The SQL statement can contain zero or more named (:name) or question mark (?) parameter markers
    public function setNamedParamMarker() { 
        $this->paramMarker = self::NAMED_PARAM_MARKER;
    }

    public function setQMarkParamMarker() {
        $this->paramMarker = self::QMARK_PARAM_MARKER;
    }

    public function setNoParamMarker() {
        $this->paramMarker = self::NO_PARAM_MARKER;
    }

    /**
     * Get param marker
     *
     * the returned value is depends on driver.
     *
     * for named parameter, this returns a key with a ":" char.
     * for question-mark parameter, this always returns a "?" char.
     *
     * @param string $key column name
     *
     * @return string
     */
    public function getParamMarker($key)
    {
        if( $this->paramMarker && $this->paramMarker === self::NAMED_PARAM_MARKER ) {
            return ':' . $key;
        } else {
            return '?';
        }
    }



    /**
     * Check driver option to quote column name
     *
     * column quote can be configured by 'quote_column' option.
     *
     * @param string $name column name
     * @return string column name with/without quotes.
     */
    abstract public function quoteColumn($name);

    /**
     * Check driver optino to quote table name
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     * @return string table name with/without quotes.
     */
    abstract public function quoteTableName($name);


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
        if ($this->quoter) {
            return call_user_func($this->quoter, $string);
        }

        // Defualt escape function, this is not safe.
        return "'" . addslashes($string) . "'";
    }

    /**
     * inflate value to SQL statement
     *
     * for example, boolean types should be translate to string TRUE or FALSE.
     */
    public function inflate($value)
    {
        if (is_array($value)) {
            return $value[0];
        }
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



