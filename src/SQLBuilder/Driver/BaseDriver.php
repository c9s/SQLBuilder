<?php
namespace SQLBuilder\Driver;
use SQLBuilder\RawValue;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ParamMarker;
use SQLBuilder\Bind;
use Closure;
use DateTime;
use Exception;
use RuntimeException;
use LogicException;

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

    public $bindAllVariables = false;

    public $paramNameCnt = 1;

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

    public function bindAllVariables($on = true) {
        $this->bindAllVariables = $on;
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


    public function quoteColumns(array $columns)
    {
        $quotedColumns = array();
        foreach($columns as $col) {
            $quotedColumns[] = $this->quoteColumn($col);
        }
        return $quotedColumns;
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


    protected function deflateScalar($value)
    {
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

        } else {
            throw new Exception("Can't deflate value, unknown type.");
        }
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

        } elseif (is_callable($value)) {

            return call_user_func($value);

        } elseif (is_object($value) ) {

            if ($value instanceof Bind) {

                if ($args) {
                    $args->add($value);
                }
                return $value->getMark();


            } elseif ($value instanceof ParamMarker) {

                if ($args) {
                    $args->add(new Bind( $value->getMark(), NULL));
                }
                return $value->getMark();

            } elseif ($value instanceof Unknown) {

                return 'UNKNOWN';

            } elseif ($value instanceof DateTime ) {

                // convert DateTime object into string
                return $value->format(DateTime::ISO8601);


            } elseif ($value instanceof ToSqlInterface) {

                return $value->toSql($this, $args);

            } elseif ($value instanceof RawValue) {

                return $value->__toString();

            } else {
                throw new LogicException('Unsupported class: ' . get_class($value));
            }

        } elseif (is_array($value) && count($value) == 1) { // raw value

            return $value[0];

        } else {
            throw new LogicException('Unsupported type');
        }
        return $value;
    }


}



