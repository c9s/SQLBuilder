<?php
namespace SQLBuilder;
use Exception;


/**
 *
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $sqlbuilder = new SQLBuilder('Member');
 *  $sqlbuilder->configure('driver','postgres');
 *  $sqlbuilder->configure('trim',true);
 *  $sqlbuilder->configure('placeholder','named');
 *  $sqlbuilder->insert(array(
 *      'foo' => 'foo',
 *      'bar' => 'bar',
 *  ));
 *  $sql = $sqlbuilder->build();
 *
 * @code
 */
class SQLBuilder 
{
    /**
     * table name 
     *
     * @var string
     * */
	public $table;

    /** 
     * limit 
     * 
     * @var integer
     * */
	public $limit;

    /**
     * offset attribute
     *
     * @var integer
     * */
	public $offset;

    /**
     * Should return result when updating or inserting?
     *
     * when this flag is set, the primary key will be returned.
     *
     * @var boolean
     */
	public $returning;

	public $where = array();
	public $orders = array();

    /**
     * selected columns
     *
     * @var string[] an array contains column names
     */
	public $selected;

	public $insert;

	public $update;

	public $behavior;


	const INSERT = 1;
	const UPDATE = 2;
	const DELETE = 3;
	const SELECT = 4;


	protected $driver = 'PDO';

    /**
     * should we quote table name in SQL ?
     */
	protected $quoteTable = true;


    /**
     * should we quote column name in SQL ?
     */
	protected $quoteColumn = true;


    /**
     * should we trim space ?
     */
	protected $trim = false;


    /**
     * get place holder
     */
	protected $placeholder = false;


    /**
     * string escaper handler
     *  
     *  Array:
     *
     *    array($obj,'method')
     */
	protected $escaper;



    /**
     *
     * @param string $table table name
     */
	public function __construct($table)
	{
		$this->table = $table;
		$this->selected = array('*');
		$this->behavior = static::SELECT;


		/**
		 * mysqli_real_escape_string:
		 *
		 *    string mysqli_real_escape_string ( mysqli $link , string $escapestr )
	     *    string pg_escape_string ([ resource $connection ], string $data )
		 *    string PDO::quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
         *
         *  $b->configure('escaper',array($pgconn,'escape_string'));
         *
		 */
		$this->escaper = 'addslashes';
	}

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
			
			case 'driver':
				$this->driver = $value;
				break;

			case 'style':
				$this->style = $value;
				break;
		}
	}


    /**
     * get table name (with quote or not)
     *
     * quotes can be used in postgresql:
     *     select * from "table_name";
     */
    public function getTableName()
    {
        if( $this->quoteTable ) {
            return '"' . $this->table . '"';
        }
        return $this->table;
    }


	public function getPlaceHolder($key)
	{
		if( $this->placeholder && $this->placeholder === 'named' ) {
			return ':' . $key;
		}
		else {
			return '?';
		}
	}


    /*** behavior methods ***/

    /**
     * update behavior 
     * 
     * @param array $args
     */
	public function update($args)
	{
		$this->update = $args;
		$this->behavior = static::UPDATE;
	}



    /**
     * select behavior
     *
     * @param array
     */
	public function select($columns)
	{
		$this->selected = (array) $columns;
		$this->behavior = static::SELECT;
	}

	/**
	 * args: column to value 
	 */
	public function insert(array $args)
	{
		$this->insert = $args;
		$this->behavior = static::INSERT;
	}


    /**
     * delete behavior
     *
     */
	public function delete()
	{
		$this->behavior = static::DELETE;
	}




    /*** limit , offset methods ***/

	public function limit($limit)
	{
		$this->limit = $limit;
	}

	public function offset($offset)
	{
		$this->offset = $offset;
	}




    /*** condition methods ***/


    /**
     *
     * style1:
     *
     * @param string column name
     * @param string condition (string or array)
     *
     * style2:
     *
     * @param array
     */
	public function where($arg1,$arg2 = null)
	{
		if( is_array($arg1) ) {
			$this->where = array_merge($this->where, $arg1 );
		}
		else {
			$this->where[ $arg1 ] = $arg2;
		}
	}


	/* for postgresql-only */
	public function returning($column)
	{
		$this->returning = $column;
	}


	public function order($column,$order = 'desc')
	{
		$this->orders[] = array( $column , $order );
	}




    /**
     * builder, protected methods
     */
	protected function buildSelectColumns()
	{
		$cols = array_map(function($item) { 
			if( preg_match('/^[a-zA-Z]$/',$item)) {
				return '"' . $item . '"';
			} else {
				return $item;
			}
		},$this->selected);
		return join(',',$cols);
	}


	/*************************
	 * public interface 
	 *************************/
	public function buildDelete()
	{
		$sql = 'DELETE FROM ' . $this->getTableName() . ' ';
		$sql .= $this->buildConditionSql();
		$sql .= $this->buildLimitSql();
		if( $this->trim )
			return trim($sql);
        return $sql;
    }


	public function buildUpdate()
	{
		$sql = 'UPDATE ' . $this->getTableName() . ' SET ';
		$sql .= $this->buildSetterSql();
		$sql .= $this->buildConditionSql();
		$sql .= $this->buildLimitSql();
		if( $this->trim )
			return trim($sql);
		return $sql;
	}


	/** 
	 * build select sql
	 */
	public function buildSelect()
	{
        /* check required arguments */
		$sql = 'SELECT ' 
			. $this->buildSelectColumns()
			. ' FROM ' . $this->getTableName() . ' ';

		$sql .= $this->buildConditionSql();

		$sql .= $this->buildOrderSql();

		$sql .= $this->buildLimitSql();

		if( $this->trim )
			return trim($sql);
        return $sql;
    }




	public function buildInsert()
	{
        /* check required arguments */
        $columns = array();
        $values = array();

        /* build sql arguments */

		if( $this->placeholder ) {
			foreach( $this->insert as $k => $v ) {
				if( is_integer($k) )
					$k = $v;
				$columns[] = $this->getQuoteColumn($k);
				$values[] = $this->getPlaceHolder($k);
			}

		} else {
			foreach( $this->insert as $k => $v ) {
				if( is_integer($k) )
					$k = $v;
				$columns[] = $this->getQuoteColumn( $k );
				$values[]  = '\'' . call_user_func( $this->escaper , $v ) . '\'';
			}
		}

        $sql = ' INSERT INTO ' . $this->getTableName() . ' ( ';
        $sql .= join(',',$columns) . ") VALUES (".  join(',', $values ) .")";

		if( $this->returning )
			$sql .= ' RETURNING ' . $this->getQuoteColumn($this->returning);

		if( $this->trim )
			return trim($sql);
        return $sql;
	}


	public function build()
	{
		if( ! $this->behavior )
			throw new Exception('behavior is not defined.');

		switch( $this->behavior )
		{
			case static::UPDATE:
				return $this->buildUpdate();
				break;
			case static::INSERT:
				return $this->buildInsert();
				break;
			case static::DELETE:
				return $this->buildDelete();
				break;
			case static::SELECT:
				return $this->buildSelect();
				break;
			default:
				throw new Exception('behavior is not defined.');
				break;
		}
	}





	protected function getQuoteColumn($name)
	{
		if( $c = $this->quoteColumn ) {
            if( is_string($c) )
                return $c . $name . $c;
            return '"' . $name . '"';
		}
		return $name;
	}


	protected function buildOrderSql()
	{
		$sql = '';
		if( !empty($this->orders) ) {
			$sql .= ' ORDER BY ';
			$parts = array();
			foreach( $this->orders as $order ) {
				list( $column , $ordering ) = $order;
				$parts[] = $this->getQuoteColumn($column) . ' ' . $ordering;
			}
			$sql .= join(',',$parts);
		}
		return $sql;
	}

	protected function buildLimitSql()
	{
		$sql = '';
		if( $this->driver == 'postgres' ) {
			if( $this->limit && $this->offset ) {
				$sql .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
			} else if ( $this->limit ) {
				$sql .= ' LIMIT ' . $this->limit;
			}
		} 
		else if( $this->driver == 'mysql' ) {
			if( $this->limit && $this->offset ) {
				$sql .= ' LIMIT ' . $this->offset . ' , ' . $this->limit;
			} else if ( $this->limit ) {
				$sql .= ' LIMIT ' . $this->limit;
			}
		}
		return $sql;
	}


	protected function buildSetterSql()
	{
        $conds = array();
		if( $this->placeholder ) {
			foreach( $this->update as $k => $v ) {
				if( is_array($v) ) {
					$conds[] = "\"$k\" = $v" ;
				} else {
					if( is_integer($k) )
						$k = $v;
					$conds[] = "\"$k\" = " . $this->getPlaceHolder($k);
				}
			}
		}
		else {
			foreach( $this->update as $k => $v ) {
				if( is_array($v) ) {
					$conds[] = "\"$k\" = $v" ;
				} else {
					$conds[] = "\"$k\" = " 
						. '\'' . call_user_func( $this->escaper , $v ) . '\'';
				}
			}
		}
		return join(', ',$conds);
	}

	protected function buildConditionSql()
	{
        $conds = array();
		if( $this->placeholder ) {
			foreach( $this->where as $k => $v ) {
				if( is_array($v) ) {
					$conds[] = "\"$k\" = $v" ;
				} else {
					if( is_integer($k) )
						$k = $v;
					$conds[] = "\"$k\" = " . $this->getPlaceHolder($k);
				}
			}
		}
		else {
			foreach( $this->where as $k => $v ) {
				if( is_array($v) ) {
					$conds[] = "\"$k\" = $v" ;
				} else {
					$conds[] = "\"$k\" = " 
						. '\'' . call_user_func( $this->escaper , $v ) . '\'';
				}
			}
		}
		$sql = '';
        if( count($conds) ) {
            $sql .= ' WHERE ' . join( ' AND ' , $conds );
        }
		return $sql;
	}



}

