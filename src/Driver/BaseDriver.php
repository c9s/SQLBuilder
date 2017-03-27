<?php

namespace SQLBuilder\Driver;

use DateTime;
use LogicException;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ParamMarker;
use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;

/**
 * Class BaseDriver
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
abstract class BaseDriver
{
    /**
     * Question mark parameter marker.
     *
     * (?,?)
     */
    const QMARK_PARAM_MARKER = 1;

    /**
     * Named parameter marker.
     */
    const NAMED_PARAM_MARKER = 2;

    public $alwaysBindValues = false;

    public $paramNameCnt = 1;

    public $paramMarkerType = self::NAMED_PARAM_MARKER;

    public $quoteTable;

    /**
     * String quoter handler.
     *
     *  Array:
     *
     *    array($obj,'method')
     */
    public $quoter;

    /**
     * @param callable $quoter
     */
    public function setQuoter(callable $quoter)
    {
        $this->quoter = $quoter;
    }

    /**
     * @param bool $on
     */
    public function alwaysBindValues($on = true)
    {
        $this->alwaysBindValues = $on;
    }

    /**
     * @param bool $enable
     */
    public function setQuoteTable($enable = true)
    {
        $this->quoteTable = $enable;
    }

    /**
     * The SQL statement can contain zero or more named (:name) or question mark (?) parameter markers
     */
    public function setNamedParamMarker()
    {
        $this->paramMarkerType = self::NAMED_PARAM_MARKER;
    }

    public function setQMarkParamMarker()
    {
        $this->paramMarkerType = self::QMARK_PARAM_MARKER;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    abstract public function quoteIdentifier($id);


    /**
     * Check driver option to quote column name.
     *
     * column quote can be configured by 'quote_column' option.
     *
     * @param string $name column name
     *
     * @return string column name with/without quotes.
     */
    public function quoteColumn($name)
    {
        // TODO: quote for DB.TABLE.COLNAME
        if (preg_match('/\W/', $name)) {
            return $name;
        }

        return $this->quoteIdentifier($name);
    }

    /**
     * Check driver optino to quote table name.
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     *
     * @return string table name with/without quotes.
     */
    public function quoteTable($name)
    {
        if ($this->quoteTable) {
            // TODO: Split DB.Table
            return $this->quoteIdentifier($name);
        }

        return $name;
    }

    /**
     * quote & escape string with single quote.
     *
     * @param $string
     *
     * @return mixed|string
     */
    public function quote($string)
    {
        /*
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

        // Default escape function, this is not safe.
        return "'" . addslashes($string) . "'";
    }

    /**
     * @param $value
     *
     * @return \SQLBuilder\Bind
     */
    public function allocateBind($value)
    {
        return new Bind('p' . $this->paramNameCnt++, $value);
    }

    /**
     * @param $value
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function deflateScalar($value)
    {
        if ($value === null) {
            return 'NULL';
        } elseif ($value === true) {
            return 'TRUE';
        } elseif ($value === false) {
            return 'FALSE';
        } elseif (is_int($value) || is_float($value)) {
            return '' . $value;
        } elseif (is_string($value)) {
            return $this->quote($value);
        } else {
            throw new \UnexpectedValueException("Can't deflate value, unknown type.");
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function cast($value)
    {
        if ($value instanceof DateTime) {
            // return $value->format(DateTime::ISO8601);
            return $value->format(DateTime::ATOM);
        }

        return $value;
    }

    /**
     * For variable placeholder like PDO, we need 1 or 0 for boolean type,.
     *
     * For pgsql and mysql sql statement,
     * we use TRUE or FALSE for boolean type.
     *
     * FOr sqlite sql statement:
     * we use 1 or 0 for boolean type.
     *
     * @param                                $value
     * @param \SQLBuilder\ArgumentArray|null $args
     *
     * @return mixed|string
     * @throws \LogicException
     */
    public function deflate($value, ArgumentArray $args = null)
    {
        if ($this->alwaysBindValues) {
            if ($value instanceof Raw) {
                return $value->__toString();
            } elseif ($value instanceof Bind) {
                if ($args) {
                    $args->bind($value);
                }

                return $value->getMarker();
            } elseif ($value instanceof ParamMarker) {
                if ($args) {
                    $args->bind(new Bind($value->getMarker(), null));
                }

                return $value->getMarker();
            } else {
                $bind = $this->allocateBind($value);
                if ($args) {
                    $args->bind($bind);
                }

                return $bind->getMarker();
            }
        }

        if ($value === null) {
            return 'NULL';
        } elseif ($value === true) {
            return 'TRUE';
        } elseif ($value === false) {
            return 'FALSE';
        } elseif (is_int($value)) {
            return (int)$value;
        } elseif (is_float($value)) {
            return (float)$value;
        } elseif (is_string($value)) {
            return $this->quote($value);
        } elseif (is_callable($value)) {
            return call_user_func($value);
        } elseif (is_object($value)) {
            if ($value instanceof Bind) {
                if ($args) {
                    $args->bind($value);
                }

                if ($this->paramMarkerType === self::QMARK_PARAM_MARKER) {
                    return '?';
                }

                /*
                elseif ($this->paramMarkerType === self::NAMED_PARAM_MARKER) {
                    return $value->getMarker();
                }
                */

                return $value->getMarker();
            } elseif ($value instanceof ParamMarker) {
                if ($args) {
                    $args->bind(new Bind($value->getMarker(), null));
                }

                if ($this->paramMarkerType === self::QMARK_PARAM_MARKER) {
                    return '?';
                }

                /*
                else if ($this->paramMarkerType === self::NAMED_PARAM_MARKER) {
                    return $value->getMarker();
                }
                */

                return $value->getMarker();
            } elseif ($value instanceof Unknown) {
                return 'UNKNOWN';
            } elseif ($value instanceof DateTime) {

                // convert DateTime object into string
                // return $this->quote($value->format(DateTime::ISO8601));
                return $this->quote($value->format(DateTime::ATOM)); // sqlite use ATOM format
            } elseif ($value instanceof ToSqlInterface) {
                return $value->toSql($this, $args);
            } elseif ($value instanceof Raw) {
                return $value->__toString();
            } else {
                throw new LogicException('Unsupported class: ' . get_class($value));
            }
        } elseif (is_array($value)) {
            // error_log("LazyRecord: deflating array type value", 0);
            return $value[0];
        } else {
            throw new LogicException('BaseDriver::deflate: Unsupported variable type');
        }
    }
}
