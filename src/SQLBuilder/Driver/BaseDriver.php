<?php
namespace SQLBuilder\Driver;
use DateTime;
use Exception;
use RuntimeException;
use LogicException;
use SQLBuilder\RawValue;

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

        } elseif ($value === true ) {

            return 'TRUE';

        } elseif ($value === false ) {

            return 'FALSE';

        } elseif (is_integer($value) ) {

            return intval($value);

        } elseif (is_float($value) ) {

            return floatval($value);

        } elseif (is_string($value) ) {

            return $this->quote($value);

        } elseif (is_object($value) ) {
            // convert DateTime object into string
            if ($value instanceof DateTime ) {

                return $value->format(DateTime::ISO8601);

            } elseif ($value instanceof ParamMarker) {

                return $value->getMark();

            } elseif ($value instanceof Bind) {

                // TODO: push value to the argument pool
                return $value->getMark();

            } else {
                throw new LogicException('Unsupported class: ' . get_class($value));
            }

        } elseif (is_array($value) && count($value) == 1) { // raw value

            return $value[0];

        } elseif ($value instanceof RawValue) {

            return $value[0]->__toString();

        } else {
            throw new LogicException('Unsupported type');
        }
        return $value;
    }


}



