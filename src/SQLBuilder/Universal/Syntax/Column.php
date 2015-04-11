<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Raw;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;
use InvalidArgumentException;


/**
 * Postgresql Data Types:
 * @see http://www.postgresql.org/docs/9.3/interactive/datatype.html
 *
 * MySQL Data Types:
 * @see http://dev.mysql.com/doc/refman/5.6/en/data-types.html
 *
 *
 * MySQL Reference

        reference_definition:
            REFERENCES tbl_name (index_col_name,...)
            [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
            [ON DELETE reference_option]
            [ON UPDATE reference_option]

        reference_option:
            RESTRICT | CASCADE | SET NULL | NO ACTION

 */
class Column implements ToSqlInterface 
{
    const  ATTR_ANY = 0;
    const  ATTR_ARRAY = 1;
    const  ATTR_STRING = 2;
    const  ATTR_INTEGER = 3;
    const  ATTR_FLOAT = 4;
    const  ATTR_CALLABLE = 5;
    const  ATTR_FLAG = 6;

    /**
     * @var string column name
     */
    public $name;


    /**
     * @var boolean primary
     */
    public $primary;

    /**
     * @var bool unsigned
     */
    public $unsigned;

    public $type;

    public $isa = 'str';

    public $null = NULL;

    /**
     * @var array is only used when isa = enum
     *
     * @MySQL
     */
    protected $enum;


    /**
     * @var array is only used when isa = set
     *
     * @MySQL
     */
    protected $set;

    /**
     * @var array $attributeTypes
     */
    protected $attributeTypes = array();


    /**
     * @var array $attributes
     *
     * The default attributes for a column.
     */
    protected $attributes = array();


    /**
     * @var string $name column name (id)
     */
    public function __construct($name = NULL, $type = NULL, array $extraAttributes = array())
    {
        $this->attributeTypes = $this->attributeTypes + array(
            'autoIncrement' => self::ATTR_FLAG,
            'unique'        => self::ATTR_FLAG, /* unique, should support by SQL syntax */
            'timezone'      => self::ATTR_FLAG,

            /**
            * When using numeric types, this property is used to save the length 
            * information, which is optional
            * @var integer
            */
            'length'   => self::ATTR_INTEGER,

            /**
            * When using numeric types, this property is used to save the decimals 
            * information, which is optional
            *
            * @var integer
            */
            'decimals'   => self::ATTR_INTEGER,

            'comment'  => self::ATTR_STRING,

            /* data type: string, integer, DateTime, classname */
            'default' => self::ATTR_ANY,
        );
        $this->name = $name;
        $this->type = $type;

        foreach ($extraAttributes as $key => $val) {
            $this->setAttribute($key, $val);
        }
    }

    public function null()
    {
        $this->null = TRUE;
        return $this;
    }

    public function notNull()
    {
        $this->null = FALSE;
        return $this;
    }




    public function name($name) 
    {
        $this->name = $name;
        return $this;
    }

    public function primary($primary = true)
    {
        $this->primary = $primary;
        return $this;
    }

    public function bit($length = 1)
    {
        $this->type = 'bit';
        $this->isa = 'int';
        $this->length = 1;
        return $this;
    }



    public function tinyInt($length = NULL) 
    {
        $this->type = 'tinyint';
        $this->isa = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function smallInt($length = NULL)
    {
        $this->type = 'smallint';
        $this->isa = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function mediumInt($length = NULL)
    {
        $this->type = 'mediumint';
        $this->isa = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function int($length = NULL)
    {
        $this->type = 'integer';
        $this->isa = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function bigint($length = NULL)
    {
        $this->type = 'bigint';
        $this->isa = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function integer($length = NULL)
    {
        return $this->int($length);
    }


    public function real($length = NULL, $decimals = NULL) {
        $this->type = 'REAL';
        $this->isa = 'double';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }


    /**
     * POINT type
     */
    public function point()
    {
        $this->type = 'double';
        $this->isa = 'double';
        return $this;
    }

    /**
     * PgSQL supports double, real.
     *
     * XXX: support for 'Infinity' '-Infinity' 'NaN'.
     *
     *
     * MySQL supports float, real, double:
     *      float(3), float, real, real(10)
     *
     * MySQL permits a nonstandard syntax: FLOAT(M,D) or REAL(M,D) or DOUBLE 
     * PRECISION(M,D). Here, “(M,D)” means than values can be stored with up 
     * to M digits in total, of which D digits may be after the decimal point. 
     * For example, a column defined as 
     *      FLOAT(7,4) will look like -999.9999 when displayed. 
     *
     * MySQL performs rounding when storing values, so if you 
     * insert 999.00009 into a FLOAT(7,4) column, the approximate result is 
     * 999.0001.
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/floating-point-types.html
     *
     * XXX: 
     * we should handle exceptions when number is out-of-range:
     * @link http://dev.mysql.com/doc/refman/5.0/en/out-of-range-and-overflow.html
     */
    public function double($length = NULL, $decimals = NULL)
    {
        $this->type = 'double';
        $this->isa = 'double';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }

    public function float($length = NULL ,$decimals = NULL)
    {
        $this->type = 'float';
        $this->isa  = 'float';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }


    public function decimal($length = NULL, $decimals = NULL)
    {
        $this->type = 'decimal';
        $this->isa = 'int';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }


    public function numeric($length = NULL, $decimals = NULL)
    {
        $this->type = 'numeric';
        $this->isa = 'int';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }

    public function unsigned($unsigned = true) 
    {
        $this->unsigned = $unsigned;
        return $this;
    }




    public function varchar($length)
    {
        $this->type = "varchar";
        $this->isa  = 'str';
        $this->length = $length;
        return $this;
    }

    public function char($length)
    {
        $this->type = "char";
        $this->isa = 'str';
        $this->length  = $length;
        return $this;
    }


    public function binary($length = NULL)
    {
        $this->type = 'binary';
        $this->isa = 'str';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function text()
    {
        $this->type = 'text';
        $this->isa = 'str';
        return $this;
    }

    public function mediumtext()
    {
        $this->type = 'MEDIUMTEXT';
        $this->isa = 'str';
        return $this;
    }

    public function longtext()
    {
        $this->type = 'LONGTEXT';
        $this->isa = 'str';
        return $this;
    }



    public function blob()
    {
        $this->type = 'blob';
        $this->isa = 'str';
        return $this;
    }

    public function tinyblob()
    {
        $this->type = 'tinyblob';
        $this->isa = 'str';
        return $this;
    }

    public function mediumblob()
    {
        $this->type = 'mediumblob';
        $this->isa = 'str';
        return $this;
    }


    public function longblob()
    {
        $this->type = 'longblob';
        $this->isa = 'str';
        return $this;
    }



    public function bool()
    {
        return $this->boolean();
    }

    public function boolean()
    {
        $this->type = 'boolean';
        $this->isa = 'bool';
        return $this;
    }


    public function enum($a)
    {
        $this->type = 'enum';
        $this->isa = 'enum';
        $this->enum = is_array($a) ? $a : func_get_args();
        return $this;
    }

    public function set($a)
    {
        $this->type = 'set';
        $this->isa = 'set';
        $this->set = is_array($a) ? $a : func_get_args();
        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        $this->type = 'integer';
        $this->isa = 'int';
        return $this;
    }


    public function year() 
    {
        $this->type = 'year';
        $this->isa = 'int';
        return $this;
    }

    public function nullDefined() {
        return $this->null !== NULL;
    }

    /**
     * serial type
     *
     * for postgresql-only
     */
    public function serial()
    {
        $this->type = 'serial';
        $this->isa = 'int';
        return $this;
    }


    /************************************************
     * DateTime related types
     *************************************************/

    public function date()
    {
        $this->type = 'date';
        $this->isa = 'DateTime'; // DateTime object
        return $this;
    }

    public function datetime()
    {
        $this->type = 'datetime';
        $this->isa = 'DateTime';
        $this->setAttribute('timezone', true);
        return $this;
    }

    public function timestamp($timezone = true)
    {
        $this->type = 'timestamp';
        $this->isa = 'DateTime';
        $this->setAttribute('timezone', $timezone);
        return $this;
    }

    public function time($timezone = true)
    {
        $this->type = 'time';
        $this->isa = 'str';
        $this->setAttribute('timezone', $timezone);
        return $this;
    }

    public function timezone($bool = true) {
        $this->setAttribute('timezone', $bool);
        return $this;
    }

    public function __isset($name)
    {
        return isset( $this->attributes[ $name ] );
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name,$value)
    {
        $this->setAttribute($name, $value);
    }

    public function __call($method,$args)
    {
        if (isset($this->attributeTypes[ $method ])) {
            $c = count($args);
            $t = $this->attributeTypes[ $method ];

            if ($t != self::ATTR_FLAG && $c == 0) {
                throw new InvalidArgumentException( 'Attribute value is required.' );
            }

            switch( $t ) {

                case self::ATTR_ANY:
                    $this->attributes[ $method ] = $args[0];
                    break;

                case self::ATTR_ARRAY:
                    if( $c > 1 ) {
                        $this->attributes[ $method ] = $args;
                    }
                    elseif( is_array($args[0]) ) 
                    {
                        $this->attributes[ $method ] = $args[0];
                    } 
                    else
                    {
                        $this->attributes[ $method ] = (array) $args[0];
                    }
                    break;

                case self::ATTR_STRING:
                    if( is_string($args[0]) ) {
                        $this->attributes[ $method ] = $args[0];
                    }
                    else {
                        throw new InvalidArgumentException("attribute value of $method is not a string.");
                    }
                    break;

                case self::ATTR_INTEGER:
                    if (is_integer($args[0])) {
                        $this->attributes[$method] = $args[0];
                    } else {
                        throw new InvalidArgumentException("attribute value of $method is not a integer.");
                    }
                    break;

                case self::ATTR_CALLABLE:

                    /**
                     * handle for __invoke, array($obj,$method), 'function_name 
                     */
                    if( is_callable($args[0]) ) {
                        $this->attributes[ $method ] = $args[0];
                    } else {
                        throw new InvalidArgumentException("attribute value of $method is not callable type.");
                    }
                    break;

                case self::ATTR_FLAG:
                    if (isset($args[0])) {
                        $this->attributes[ $method ] = $args[0];
                    } else {
                        $this->attributes[ $method ] = true;
                    }
                    break;

                default:
                    throw new InvalidArgumentException("Unsupported attribute type: $method");
            }
            return $this;
        }

        // save unknown attribute by default
        $this->attributes[ $method ] = ! empty($args) ? $args[0] : NULL;
        return $this;
    }


    /**
     * Which should be something like getAttribute($name)
     *
     * @param string $name attribute name
     */
    public function getAttribute($name) 
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    }

    public function setAttribute($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        }
        $this->attributes[$name] = $value;
        return $this;
    }


    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function buildNullClause(BaseDriver $driver) 
    {
        if ($this->null === FALSE) {
            return ' NOT NULL';
        } elseif ($this->null === TRUE) {
            return  ' NULL';
        }
        return '';
    }

    public function buildDefaultClause(BaseDriver $driver)
    {
        // Build default value
        if (($default = $this->default) !== NULL) { 
            if (is_callable($default)) {
                return ' DEFAULT ' . call_user_func($this->default, $this, $driver);
            } else {
                return ' DEFAULT ' . $driver->deflate($default);
            }
        }
        return '';
    }

    public function buildPgSQLDefinitionSql(BaseDriver $driver, ArgumentArray $args)
    {
        $isa  = $this->isa ?: 'str';

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);

        if ($this->autoIncrement) {
            $sql .= ' SERIAL';
        } else {
            $sql .= $this->buildTypeSql($driver);
        }

        if ($this->unsigned) {
            $sql .= ' UNSIGNED';
        }

        $sql .= $this->buildNullClause($driver);
        $sql .= $this->buildDefaultClause($driver);
        return $sql;
    }

    public function buildTypeSql(BaseDriver $driver)
    {
        $sql = '';
        if ($this->type) {
            $sql .= ' ' . $this->type;
            if (isset($this->length) && isset($this->decimals)) {
                $sql .= '(' . $this->length . ',' . $this->decimals . ')';
            } elseif (isset($this->length)) {
                $sql .= '(' . $this->length . ')';
            }
        }
        return $sql;
    }

    public function buildDefinitionSql(BaseDriver $driver, ArgumentArray $args)
    {
        $isa  = $this->isa ?: 'str';

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);

        $sql .= $this->buildTypeSql($driver);

        if ($this->unsigned) {
            $sql .= ' UNSIGNED';
        }



        if ($isa === 'enum' && !empty($this->enum)) {
            $enum = array();
            foreach ($this->enum as $val) {
                $enum[] = $driver->deflate($val);
            }
            $sql .= '(' . join(', ', $enum) . ')';
        } elseif ($isa === 'set' && !empty($this->set)) {

            $set = array();
            foreach ($this->set as $val) {
                $set[] = $driver->deflate($val);
            }
            $sql .= '(' . join(', ', $set) . ')';

        }

        $sql .= $this->buildNullClause($driver);
        $sql .= $this->buildDefaultClause($driver);


        if ($this->primary) {
            $sql .= ' PRIMARY KEY';

        }

        if ($this->autoIncrement) {
            if ($driver instanceof SQLiteDriver) {
                $sql .= ' AUTOINCREMENT';
            } elseif ($driver instanceof MySQLDriver) {
                $sql .= ' AUTO_INCREMENT';
            }
        }

        if ($this->unique) {
            $sql .= ' UNIQUE';
        }

        if ($this->comment) {
            $sql .= ' COMMENT ' . $driver->deflate($this->comment);
        }
        return $sql;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        if ($driver instanceof MySQLDriver || $driver instanceof SQLiteDriver ) {
            return $this->buildDefinitionSql($driver, $args);
        } elseif ($driver instanceof PgSQLDriver) {
            return $this->buildPgSQLDefinitionSql($driver, $args);
        } else {
            throw new UnsupportedDriverException;
        }
        return '';
    }

    /*********************************************************************
     * PROTECTED METHODS (internal use)
     ***********************************************************************/
    protected function setLengthInfo($length, $decimals = NULL) {
        $this->length($length);
        if ($decimals) {
            $this->decimals($decimals);
        }
    }
}

