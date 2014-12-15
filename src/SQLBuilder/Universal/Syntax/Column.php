<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;


/**
 * Postgresql Data Types:
 * @see http://www.postgresql.org/docs/9.3/interactive/datatype.html
 *
 * MySQL Data Types:
 * @see http://dev.mysql.com/doc/refman/5.6/en/data-types.html
 */
class Column implements ToSqlInterface {
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
     * When using numeric types, this property is used to save the length 
     * information, which is optional
     *
     * @var integer
     */
    protected $length;

    /**
     * When using numeric types, this property is used to save the decimals 
     * information, which is optional
     *
     * @var integer
     */
    protected $decimals;

    protected $type = 'text'; 

    protected $isa = 'str';


    /**
     * @var array $supportedAttributes
     */
    public $supportedAttributes = array();

    /**
     * @var array $attributes
     *
     * The default attributes for a column.
     */
    public $attributes = array();

    /**
     * @var string $name column name (id)
     */
    public function __construct($name)
    {
        $this->supportedAttributes = array(
            'primary'       => self::ATTR_FLAG,
            'size'          => self::ATTR_INTEGER,
            'autoIncrement' => self::ATTR_FLAG,
            'immutable'     => self::ATTR_FLAG,
            'unique'        => self::ATTR_FLAG, /* unique, should support by SQL syntax */
            'null'          => self::ATTR_FLAG,
            'notNull'       => self::ATTR_FLAG,
            'required'      => self::ATTR_FLAG,
            'timezone'      => self::ATTR_FLAG,
            'enum'          => self::ATTR_ARRAY,

            'comment'  => self::ATTR_STRING,

            /* data type: string, integer, DateTime, classname */
            'isa' => self::ATTR_STRING,

            'type' => self::ATTR_STRING,

            'default' => self::ATTR_ANY,

            /* primary field for CMS */
            'primaryField' => self::ATTR_FLAG,
        );
        $this->name = $name;
    }




    public function name($name) 
    {
        $this->name = $name;
        return $this;
    }

    public function varchar($length)
    {
        $this->type = "varchar($length)";
        $this->isa  = 'str';
        $this->length = $length;
        return $this;
    }

    public function char($length)
    {
        $this->type = "char($length)";
        $this->isa = 'str';
        $this->length  = $length;
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
    public function double($length = null, $decimals = null)
    {
        $this->type = 'double';
        $this->isa = 'double';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }

    public function float($length = null ,$decimals = null)
    {
        $this->type = 'float';
        $this->isa  = 'float';
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
        return $this;
    }

    public function tinyInt($length = NULL) 
    {
        $this->attributes['type'] = 'tinyint';
        $this->attributes['isa'] = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function smallInt($length = NULL)
    {
        $this->attributes['type'] = 'smallint';
        $this->attributes['isa'] = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function mediumInt($length = NULL)
    {
        $this->attributes['type'] = 'mediumint';
        $this->attributes['isa'] = 'int';
        if ($length) {
            $this->length = $length;
        }
        return $this;
    }

    public function int($length = NULL)
    {
        $this->attributes['type'] = 'mediumint';
        $this->attributes['isa'] = 'int';
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


    public function text()
    {
        $this->attributes['type'] = 'text';
        $this->attributes['isa'] = 'str';
        return $this;
    }

    public function bool()
    {
        return $this->boolean();
    }

    public function boolean()
    {
        $this->attributes['type'] = 'boolean';
        $this->attributes['isa'] = 'bool';
        return $this;
    }

    public function blob()
    {
        $this->attributes['type'] = 'blob';
        $this->attributes['isa'] = 'str';
        return $this;
    }

    public function binary()
    {
        $this->attributes['type'] = 'binary';
        $this->attributes['isa'] = 'str';
        return $this;
    }

    public function enum()
    {
        $this->attributes['type'] = 'enum';
        $this->attributes['isa'] = 'enum';
        $this->attributes['enum'] = func_get_args();
        return $this;
    }

    /**
     * serial type
     *
     * for postgresql-only
     */
    public function serial()
    {
        $this->attributes['type'] = 'serial';
        $this->attributes['isa'] = 'int';
        return $this;
    }


    /************************************************
     * DateTime related types
     *************************************************/

    public function date()
    {
        $this->attributes['type'] = 'date';
        $this->attributes['isa'] = 'DateTime';
        return $this;
    }

    public function time() 
    {
        $this->attributes['type'] = 'time';
        $this->attributes['isa'] = 'str';
        $this->attributes['timezone'] = true;
        return $this;
    }

    public function timezone($bool = true) {
        $this->attributes['timezone'] = $bool;
        return $this;
    }

    public function datetime()
    {
        $this->attributes['type'] = 'datetime';
        $this->attributes['isa'] = 'DateTime';
        $this->attributes['timezone'] = true;
        return $this;
    }

    public function timestamp()
    {
        $this->attributes['type'] = 'timestamp';
        $this->attributes['isa'] = 'DateTime';
        $this->attributes['timezone'] = true;
        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        $this->type = 'integer';
        $this->isa = 'int';
        return $this;
    }

    public function index($indexName = null) {
        $this->attributes['index'] = $indexName ?: true;
        return $this;
    }

    public function export()
    {
        return array(
            'name' => $this->name,
            'attributes' => $this->attributes,
        );
    }

    public function toArray()
    {
        $attrs = $this->attributes;
        $attrs['name'] = $this->name;
        return $attrs;
    }

    public function dump()
    {
        return var_export( $this->export() , true );
    }

    public function __isset($name)
    {
        return isset( $this->attributes[ $name ] );
    }

    public function __get($name)
    {
        if( isset( $this->attributes[ $name ] ) )
            return $this->attributes[ $name ];
    }

    public function __set($name,$value)
    {
        $this->attributes[ $name ] = $value;
    }

    public function __call($method,$args)
    {
        if( isset($this->supportedAttributes[ $method ] ) ) {
            $c = count($args);
            $t = $this->supportedAttributes[ $method ];

            if( $t != self::ATTR_FLAG && $c == 0 ) {
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
                    if( is_integer($args[0])) {
                        $this->attributes[ $method ] = $args[0];
                    }
                    else {
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
                    if( isset($args[0]) ) {
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
        $this->attributes[ $method ] = ! empty($args) ? $args[0] : null;
        return $this;
    }


    /**
     * Which should be something like getAttribute($name)
     *
     * @param string $name attribute name
     */
    public function get($name) 
    {
        if ( isset($this->attributes[$name]) ) {
            return $this->attributes[$name];
        }
    }

    public function getName() {
        return $this->name;
    }

    public function getDefaultValue( $record = null, $args = null )
    {
        // XXX: might contains array() which is a raw sql statement.
        if ($val = $this->get('default') ) {
            return Utils::evaluate( $val , array($record, $args));
        }
    }


    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        return '';
    }



    /*********************************************************************
     * PROTECTED METHODS (internal use)
     ***********************************************************************/
    protected function setLengthInfo($length, $decimals = NULL) {
        $this->length = $length;
        if ($decimals) {
            $this->decimals = $decimals;
        }
    }

    protected function setLength($length) {
        $this->length = $length;
    }

    protected function setDecimals($decimals) {
        $this->decimals = $decimals;
        return $this;
    }
}

