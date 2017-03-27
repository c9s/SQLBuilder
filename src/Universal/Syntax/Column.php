<?php

namespace SQLBuilder\Universal\Syntax;

use Closure;
use InvalidArgumentException;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\ToSqlInterface;

/**
 * Postgresql Data Types:.
 *
 * @see http://www.postgresql.org/docs/9.3/interactive/datatype.html
 *
 * MySQL Data Types:
 * @see http://dev.mysql.com/doc/refman/5.6/en/data-types.html
 *
 *
 * MySQL Reference
 */
class Column implements ToSqlInterface
{
    const  ATTR_ANY      = 0;
    const  ATTR_ARRAY    = 1;
    const  ATTR_STRING   = 2;
    const  ATTR_INTEGER  = 3;
    const  ATTR_FLOAT    = 4;
    const  ATTR_CALLABLE = 5;
    const  ATTR_FLAG     = 6;

    /**
     * @var string column name
     */
    public $name;

    /**
     * @var bool primary
     */
    public $primary;

    /**
     * @var bool unsigned
     */
    public $unsigned;

    public $type;

    public $isa = 'str';

    // Null is set to true by default. (I know MySQL set this as default if you don't specify the not null constraint)
    public $notNull;

    /**
     * @var array is only used when isa = enum
     *
     * @MySQL
     */
    public $enum;

    /**
     * @var array is only used when isa = set
     *
     * @MySQL
     */
    public $set;

    /**
     * @var string right now this is only for timestamp column
     *
     * @MySQL
     */
    public $onUpdate;

    /**
     * @var array
     */
    protected $attributeTypes = [];

    /**
     * @var array
     *
     * The default attributes for a column.
     */
    protected $attributes = [];

    /**
     * @param null  $name
     * @param null  $type
     * @param array $extraAttributes
     *
     * @internal param string $ column name (id)
     */
    public function __construct($name = null, $type = null, array $extraAttributes = [])
    {
        $this->attributeTypes += [
            'unique'   => self::ATTR_FLAG, /* unique, should support by SQL syntax */
            'timezone' => self::ATTR_FLAG,

            /*
            * When using numeric types, this property is used to save the length
            * information, which is optional
            * @var integer
            */
            'length'   => self::ATTR_INTEGER,

            /*
            * When using numeric types, this property is used to save the decimals
            * information, which is optional
            *
            * @var integer
            */
            'decimals' => self::ATTR_INTEGER,

            'comment' => self::ATTR_STRING,

            // 'default' is here because we can't name a method with 'default' in PHP.
            'default' => self::ATTR_ANY,
        ];
        $this->name           = $name;
        $this->type           = $type;

        foreach ($extraAttributes as $key => $val) {
            $this->setAttribute($key, $val);
        }
    }

    public function type($type)
    {
        if ($type === 'integer') {
            $type = 'int';
        }
        $this->type = $type;

        return $this;
    }

    public function isa($isa)
    {
        $this->isa = $isa;

        return $this;
    }

    public function null()
    {
        $this->notNull = false;

        return $this;
    }

    public function notNull()
    {
        $this->notNull = true;

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
        $this->type   = 'bit';
        $this->isa    = 'int';
        $this->length = $length;

        return $this;
    }

    public function tinyInt($length = null)
    {
        $this->type = 'tinyint';
        $this->isa  = 'int';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function smallInt($length = null)
    {
        $this->type = 'smallint';
        $this->isa  = 'int';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function mediumInt($length = null)
    {
        $this->type = 'mediumint';
        $this->isa  = 'int';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function int($length = null)
    {
        $this->type = 'int';
        $this->isa  = 'int';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function bigint($length = null)
    {
        $this->type = 'bigint';
        $this->isa  = 'int';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function integer($length = null)
    {
        return $this->int($length);
    }

    public function real($length = null, $decimals = null)
    {
        $this->type = 'REAL';
        $this->isa  = 'double';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }

        return $this;
    }

    /**
     * POINT type.
     */
    public function point()
    {
        $this->type = 'double';
        $this->isa  = 'double';

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
     *
     * @param null $length
     * @param null $decimals
     *
     * @return $this
     */
    public function double($length = null, $decimals = null)
    {
        $this->type = 'double';
        $this->isa  = 'double';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }

        return $this;
    }

    public function float($length = null, $decimals = null)
    {
        $this->type = 'float';
        $this->isa  = 'float';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }

        return $this;
    }

    public function decimal($length = null, $decimals = null)
    {
        $this->type = 'decimal';
        $this->isa  = 'int';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }

        return $this;
    }

    public function numeric($length = null, $decimals = null)
    {
        $this->type = 'numeric';
        $this->isa  = 'int';
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
        $this->type   = 'varchar';
        $this->isa    = 'str';
        $this->length = $length;

        return $this;
    }

    public function char($length)
    {
        $this->type   = 'char';
        $this->isa    = 'str';
        $this->length = $length;

        return $this;
    }

    public function binary($length = null)
    {
        $this->type = 'binary';
        $this->isa  = 'str';
        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    public function text()
    {
        $this->type = 'text';
        $this->isa  = 'str';

        return $this;
    }

    public function mediumtext()
    {
        $this->type = 'MEDIUMTEXT';
        $this->isa  = 'str';

        return $this;
    }

    public function longtext()
    {
        $this->type = 'LONGTEXT';
        $this->isa  = 'str';

        return $this;
    }

    public function blob()
    {
        $this->type = 'blob';
        $this->isa  = 'str';

        return $this;
    }

    public function tinyblob()
    {
        $this->type = 'tinyblob';
        $this->isa  = 'str';

        return $this;
    }

    public function mediumblob()
    {
        $this->type = 'mediumblob';
        $this->isa  = 'str';

        return $this;
    }

    public function longblob()
    {
        $this->type = 'longblob';
        $this->isa  = 'str';

        return $this;
    }

    public function bool()
    {
        return $this->boolean();
    }

    public function boolean()
    {
        $this->type = 'boolean';
        $this->isa  = 'bool';

        return $this;
    }

    public function enum($a)
    {
        $this->type = 'enum';
        $this->isa  = 'enum';
        $this->enum = is_array($a) ? $a : func_get_args();

        return $this;
    }

    public function set($a)
    {
        $this->type = 'set';
        $this->isa  = 'set';
        $this->set  = is_array($a) ? $a : func_get_args();

        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        $this->isa           = 'int';
        if (!$this->type) {
            $this->type     = 'int';
            $this->unsigned = true;
        }

        return $this;
    }

    public function year()
    {
        $this->type = 'year';
        $this->isa  = 'int';

        return $this;
    }

    public function nullDefined()
    {
        return $this->notNull !== null;
    }

    /**
     * serial type.
     *
     * for postgresql-only
     */
    public function serial()
    {
        $this->type = 'serial';
        $this->isa  = 'int';

        return $this;
    }

    /************************************************
     * DateTime related types
     *************************************************/

    public function date()
    {
        $this->type = 'date';
        $this->isa  = 'DateTime'; // DateTime object

        return $this;
    }

    public function datetime()
    {
        $this->type = 'datetime';
        $this->isa  = 'DateTime';
        $this->setAttribute('timezone', true);

        return $this;
    }

    /**
     * mysql timestamp automatic initialization.
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/timestamp-initialization.html
     *
     * @param bool $timezone
     *
     * @return $this
     */
    public function timestamp($timezone = true)
    {
        $this->type = 'timestamp';
        // Disable not null for simpilicity
        // $this->notNull = true;
        $this->isa = 'DateTime';
        $this->setAttribute('timezone', $timezone);

        return $this;
    }

    public function time($timezone = true)
    {
        $this->type = 'time';
        $this->isa  = 'str';
        $this->setAttribute('timezone', $timezone);

        return $this;
    }

    public function timezone($bool = true)
    {
        $this->setAttribute('timezone', $bool);

        return $this;
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __call($method, $args)
    {
        if (isset($this->attributeTypes[$method])) {
            $c = count($args);
            $t = $this->attributeTypes[$method];

            if ($t != self::ATTR_FLAG && $c == 0) {
                throw new InvalidArgumentException('Attribute value is required.');
            }

            switch ($t) {

                case self::ATTR_ANY:
                    $this->attributes[$method] = $args[0];
                    break;

                case self::ATTR_ARRAY:
                    if ($c > 1) {
                        $this->attributes[$method] = $args;
                    } elseif (is_array($args[0])) {
                        $this->attributes[$method] = $args[0];
                    } else {
                        $this->attributes[$method] = (array)$args[0];
                    }
                    break;

                case self::ATTR_STRING:
                    if (is_string($args[0])) {
                        $this->attributes[$method] = $args[0];
                    } else {
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

                    /*
                     * handle for __invoke, array($obj,$method), 'function_name 
                     */
                    if (is_callable($args[0])) {
                        $this->attributes[$method] = $args[0];
                    } else {
                        throw new InvalidArgumentException("attribute value of $method is not callable type.");
                    }
                    break;

                case self::ATTR_FLAG:
                    if (isset($args[0])) {
                        $this->attributes[$method] = $args[0];
                    } else {
                        $this->attributes[$method] = true;
                    }
                    break;

                default:
                    throw new InvalidArgumentException("Unsupported attribute type: $method");
            }

            return $this;
        }

        // save unknown attribute by default
        $this->attributes[$method] = !empty($args) ? $args[0] : null;

        return $this;
    }

    /**
     * Which should be something like getAttribute($name).
     *
     * @param string $name attribute name
     */
    public function getAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    }

    public function setAttributeStash(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $val) {
            $this->setAttribute($key, $val);
        }
    }

    public function setAttribute($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;

            return $this;
        }
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    // ***** Clause builder *****

    public function buildNullClause(BaseDriver $driver)
    {
        if ($this->notNull) {
            return ' NOT NULL';
        } elseif ($this->notNull === false) {
            return ' NULL';
        }

        return '';
    }

    public function buildUnsignedClause(BaseDriver $driver)
    {
        if ($driver instanceof SQLiteDriver || $driver instanceof PgSQLDriver) {
            // unsigned primary key is not supported in SQLiteDriver and PgSQLDriver
            return '';
        }
        if ($this->unsigned) {
            return ' UNSIGNED';
        }

        return '';
    }

    public function buildDefaultClause(BaseDriver $driver)
    {
        $sql = '';
        // Build default value
        if (($default = $this->default) !== null) {
            // When user defines a closure, it means the default value is
            // lazily provided, don't build the closure value for SQL
            // statement.
            if (!is_callable($default) && !$default instanceof Closure) {
                $sql .= ' DEFAULT ' . $driver->deflate($default);
            }
        }
        if ($this->onUpdate && $driver instanceof MySQLDriver) {
            $sql .= ' ON UPDATE ' . $driver->deflate($this->onUpdate);
        }

        return $sql;
    }

    public function buildTimeZoneClause(BaseDriver $driver)
    {
        if ($driver instanceof PgSQLDriver && $this->timezone) {
            return ' WITH TIME ZONE';
        }

        return '';
    }

    public function buildEnumClause(BaseDriver $driver)
    {
        if ($this->isa === 'enum' && !empty($this->enum)) {
            $enum = [];
            foreach ($this->enum as $val) {
                $enum[] = $driver->deflate($val);
            }

            return '(' . implode(', ', $enum) . ')';
        }

        return '';
    }

    public function buildSetClause(BaseDriver $driver)
    {
        if ($this->isa === 'set' && !empty($this->set)) {
            $set = [];
            foreach ($this->set as $val) {
                $set[] = $driver->deflate($val);
            }

            return '(' . implode(', ', $set) . ')';
        }

        return '';
    }

    public function buildTypeName(BaseDriver $driver)
    {
        $type = $this->type;
        if ($driver instanceof SQLiteDriver) {
            switch ($type) {
                case 'int':
                    $type = 'INTEGER'; // sqlite requires auto-increment on "INTEGER"
                    break;
            }
        }
        if (isset($this->length) && isset($this->decimals)) {
            return $type . '(' . $this->length . ',' . $this->decimals . ')';
        } elseif (isset($this->length)) {
            return $type . '(' . $this->length . ')';
        }

        return $type;
    }

    public function buildPrimaryKeyClause(BaseDriver $driver)
    {
        if ($this->primary) {
            return ' PRIMARY KEY';
        }

        return '';
    }

    public function buildUniqueClause(BaseDriver $driver)
    {
        if ($this->unique) {
            return ' UNIQUE';
        }

        return '';
    }

    public function buildAutoIncrementClause(BaseDriver $driver)
    {
        if ($this->autoIncrement) {
            if ($driver instanceof SQLiteDriver) {
                return ' AUTOINCREMENT';
            } elseif ($driver instanceof MySQLDriver) {
                return ' AUTO_INCREMENT';
            }
        }

        return '';
    }

    public function buildTypeClause(BaseDriver $driver)
    {
        if ($driver instanceof PgSQLDriver) {
            if ($this->autoIncrement) {
                return ' SERIAL';
            }
        }

        $sql = ' ' . $this->buildTypeName($driver);

        if ($driver instanceof MySQLDriver) {
            if ($this->isa === 'enum' && !empty($this->enum)) {
                $sql .= $this->buildEnumClause($driver);
            } elseif ($this->isa === 'set' && !empty($this->set)) {
                $sql .= $this->buildSetClause($driver);
            }
        }

        return $sql;
    }

    public function buildPgSQLDefinitionSql(BaseDriver $driver, ArgumentArray $args)
    {
        $isa = $this->isa ?: 'str';

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);
        $sql .= $this->buildTypeClause($driver);
        $sql .= $this->buildTimeZoneClause($driver);
        $sql .= $this->buildUnsignedClause($driver);
        $sql .= $this->buildNullClause($driver);
        $sql .= $this->buildDefaultClause($driver);

        return $sql;
    }

    public function buildDefinitionSqlForModify(BaseDriver $driver, ArgumentArray $args)
    {
        $isa = $this->isa ?: 'str';

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);
        $sql .= $this->buildTypeClause($driver);
        $sql .= $this->buildUnsignedClause($driver);
        $sql .= $this->buildNullClause($driver);
        $sql .= $this->buildDefaultClause($driver);
        $sql .= $this->buildAutoIncrementClause($driver);
        if ($this->comment) {
            $sql .= ' COMMENT ' . $driver->deflate($this->comment);
        }

        return $sql;
    }

    public function buildDefinitionSql(BaseDriver $driver, ArgumentArray $args)
    {
        $isa = $this->isa ?: 'str';

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);

        $sql .= $this->buildTypeClause($driver);
        $sql .= $this->buildUnsignedClause($driver);
        $sql .= $this->buildNullClause($driver);
        $sql .= $this->buildDefaultClause($driver);
        $sql .= $this->buildPrimaryKeyClause($driver);
        $sql .= $this->buildAutoIncrementClause($driver);
        $sql .= $this->buildUniqueClause($driver);

        if ($this->comment) {
            $sql .= ' COMMENT ' . $driver->deflate($this->comment);
        }

        return $sql;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($driver instanceof MySQLDriver || $driver instanceof SQLiteDriver) {
            return $this->buildDefinitionSql($driver, $args);
        } elseif ($driver instanceof PgSQLDriver) {
            return $this->buildPgSQLDefinitionSql($driver, $args);
        } else {
            throw new UnsupportedDriverException($driver, $this);
        }

        return '';
    }

    /*********************************************************************
     * PROTECTED METHODS (internal use)
     ***********************************************************************/
    protected function setLengthInfo($length, $decimals = null)
    {
        $this->length($length);
        if ($decimals) {
            $this->decimals($decimals);
        }
    }

    public function __get_state()
    {
        $vars = get_object_vars($this);
        unset($vars['attributeTypes']);
        $vars = array_filter($vars);

        return $vars;
    }

    public static function __set_state(array $stash)
    {
        $column = new self($stash['name'], $stash['type']);
        if (isset($stash['primary'])) {
            $column->primary = $stash['primary'];
        }
        if (isset($stash['unsigned'])) {
            $column->unsigned = $stash['unsigned'];
        }
        if (isset($stash['type'])) {
            $column->type = $stash['type'];
        }
        if (isset($stash['isa'])) {
            $column->isa = $stash['isa'];
        }
        if (isset($stash['notNull'])) {
            $column->notNull = $stash['notNull'];
        }
        if (isset($stash['enum']) && $stash['enum']) {
            $column->enum($stash['enum']);
        }
        if (isset($stash['set']) && $stash['set']) {
            $column->set($stash['set']);
        }
        $column->setAttributeStash($stash['attributes']);

        return $column;
    }
}
