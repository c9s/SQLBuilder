<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;



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
    protected $supportedAttributes = array();

    /**
     * @var array $attributes
     *
     * The default attributes for a column.
     */
    protected $attributes = array();





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






    public function bit($length = NULL)
    {

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
        $this->type = 'mediumint';
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
        $this->type = 'real';
        $this->isa = 'int'; // XXX: correct type?
        if ($length) {
            $this->setLengthInfo($length, $decimals);
        }
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



    public function text()
    {
        $this->type = 'text';
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

    public function blob()
    {
        $this->type = 'blob';
        $this->isa = 'str';
        return $this;
    }

    public function binary()
    {
        $this->type = 'binary';
        $this->isa = 'str';
        return $this;
    }

    public function enum()
    {
        $this->type = 'enum';
        $this->isa = 'enum';
        $this->enum = func_get_args();
        return $this;
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
        $this->isa = 'DateTime';
        return $this;
    }

    public function time() 
    {
        $this->type = 'time';
        $this->isa = 'str';
        $this->set('timezone', $bool);
        return $this;
    }

    public function timezone($bool = true) {
        $this->set('timezone', $bool);
        return $this;
    }

    public function datetime()
    {
        $this->type = 'datetime';
        $this->isa = 'DateTime';
        $this->set('timezone', true);
        return $this;
    }

    public function timestamp()
    {
        $this->type = 'timestamp';
        $this->isa = 'DateTime';
        $this->set('timezone', true);
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
        $this->set('index', $indexName ?: true);
        return $this;
    }

    public function __isset($name)
    {
        return isset( $this->attributes[ $name ] );
    }

    public function __get($name)
    {
        if ( isset( $this->attributes[ $name ] ) ) {
            return $this->attributes[ $name ];
        }
    }

    public function __set($name,$value)
    {
        $this->attributes[ $name ] = $value;
    }

    public function __call($method,$args)
    {
        if (isset($this->supportedAttributes[ $method ])) {
            $c = count($args);
            $t = $this->supportedAttributes[ $method ];

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
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    }

    public function set($name, $value) {
        $this->attributes[ $name ] = $value;
        return $this;
    }


    public function getName() {
        return $this->name;
    }




    /**
    Build reference

    track(
        FOREIGN KEY(trackartist) REFERENCES artist(artistid)
        artist_id INTEGER REFERENCES artist
    )

    MySQL Syntax:
    
        reference_definition:

        REFERENCES tbl_name (index_col_name,...)
            [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
            [ON DELETE reference_option]
            [ON UPDATE reference_option]

        reference_option:
            RESTRICT | CASCADE | SET NULL | NO ACTION

    A reference example:

    PRIMARY KEY (`idEmployee`) ,
    CONSTRAINT `fkEmployee_Addresses`
    FOREIGN KEY `fkEmployee_Addresses` (`idAddresses`)
    REFERENCES `schema`.`Addresses` (`idAddresses`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION

    */
    public function buildSqlMySQl(BaseDriver $driver, ArgumentArray $args)
    {

        $isa  = $this->isa ?: 'str';
        $type = $this->type;
        if ( ! $type && $isa == 'str' ) {
            $type = 'text'; // set the default type to text.
        }

        // format length to SQL
        if ($this->length && $this->decimals) {
            $type .= '(' . $this->length . ',' . $this->decimals . ')';
        } elseif ($this->length) {
            $type .= '(' . $this->length . ')';
        }

        $sql = '';
        $sql .= $driver->quoteIdentifier($this->name);


        $sql .= ' ' . $type;



        if ($isa === 'enum' && !empty($this->enum)) {
            $enum = array();
            foreach ($this->enum as $val) {
                $enum[] = $driver->deflate($val);
            }
            $sql .= '(' . join(', ', $enum) . ')';
        }


        if ($this->required || $this->notNull ) {
            $sql .= ' NOT NULL';
        } elseif ($this->null) {
            $sql .= ' NULL';
        }

        // Build default value
        if (($default = $this->default) !== null && ! is_callable($this->default )) { 
            // raw sql default value

            if ($default instanceof Raw) {

                $sql .= ' DEFAULT ' . $default[0];

            } elseif (is_callable($default)) {

                $sql .= ' DEFAULT ' . call_user_func($this->default, $this);

            } elseif (is_array($default)) {

                // TODO: remove raw value by array type here to support 'set' and 'enum'
                $sql .= ' DEFAULT ' . $default;

            } else {
                $sql .= ' DEFAULT ' . $driver->deflate($default);
            }
        }

        if ($this->primary) {
            $sql .= ' PRIMARY KEY';

        }

        if ($this->autoIncrement) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($this->unique) {
            $sql .= ' UNIQUE';
        }



        if ($this->comment) {
            $sql .= ' COMMENT ' . $driver->defalte($this->comment);
        }

        /*
        foreach( $schema->relations as $rel ) {
            switch( $rel['type'] ) {
            case SchemaDeclare::belongs_to:
            case SchemaDeclare::has_many:
            case SchemaDeclare::has_one:
                if( $name != 'id' && $rel['self_column'] == $name ) 
                {
                    $fSchema = new $rel['foreign_schema'];
                    $fColumn = $rel['foreign_column'];
                    $fc = $fSchema->columns[$fColumn];
                    $sql .= ' REFERENCES ' . $fSchema->getTable() . '(' . $fColumn . ')';
                }
                break;
            }
        }
         */
        return $sql;
    }


    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        if ($driver instanceof MySQLDriver) {
            return $this->buildSqlMySQl($driver, $args);
        } else {
            throw new UnsupportedDriverException;
        }
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

