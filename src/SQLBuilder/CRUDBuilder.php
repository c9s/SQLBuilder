<?php
namespace SQLBuilder;
use Exception;

/**
 *
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $sqlbuilder = new SQLBuilder\CRUDBuilder('Member');
 *
 *  $sqlbuilder->insert(array(
 *       // placeholder => 'value'
 *      'foo' => 'foo',
 *      'bar' => 'bar',
 *  ));
 *  $sqlbuilder->insert(array(
 *      'foo',
 *      'bar',
 *  ));
 *  $sql = $sqlbuilder->build();
 *
 * @code
 */
class CRUDBuilder 
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

    /* sql driver */
    public $driver;

	public $where;
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




    /**
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
	}

    /**
     * get table name (with quote or not)
     *
     * quotes can be used in postgresql:
     *     select * from "table_name";
     */
    public function getTableName()
    {
        if( $this->driver->quoteTable ) {
            return '"' . $this->table . '"';
        }
        return $this->table;
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
        return $this;
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
        return $this;
	}

	/**
	 * args: column to value 
	 */
	public function insert(array $args)
	{
		$this->insert = $args;
		$this->behavior = static::INSERT;
        return $this;
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
        return $this;
	}

	public function offset($offset)
	{
		$this->offset = $offset;
        return $this;
	}




    /*** condition methods ***/

	public function where()
	{
        $this->where = $expr = new Expression;
        $expr->driver = $this->driver;
        return $expr;
	}


    /**
     * build expressions from arguments for simple usage.
     *
     * @param array
     */
    public function whereFromArgs($args)
    {
        $expr = $this->where();
        foreach( $args as $k => $v ) {
            $expr = $expr->isEqual( $k , $v );
        }
        return $this;
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
		if( $this->driver->trim )
			return trim($sql);
        return $sql;
    }


	public function buildUpdate()
	{
		$sql = 'UPDATE ' . $this->getTableName() . ' SET ';
		$sql .= $this->buildSetterSql();
		$sql .= $this->buildConditionSql();
		$sql .= $this->buildLimitSql();
		if( $this->driver->trim )
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

		if( $this->driver->trim )
			return trim($sql);
        return $sql;
    }




	public function buildInsert()
	{
        /* check required arguments */
        $columns = array();
        $values = array();

        /* build sql arguments */

		if( $this->driver->placeholder ) {
			foreach( $this->insert as $k => $v ) {
				if( is_integer($k) )
					$k = $v;
				$columns[] = $this->driver->getQuoteColumn($k);
				$values[] = $this->driver->getPlaceHolder($k);
			}

		} else {
			foreach( $this->insert as $k => $v ) {
				if( is_integer($k) )
					$k = $v;
				$columns[] = $this->driver->getQuoteColumn( $k );
				$values[]  = '\'' . call_user_func( $this->driver->escaper , $v ) . '\'';
			}
		}

        $sql = ' INSERT INTO ' . $this->getTableName() . ' ( ';
        $sql .= join(',',$columns) . ") VALUES (".  join(',', $values ) .")";

		if( $this->returning )
			$sql .= ' RETURNING ' . $this->driver->getQuoteColumn($this->returning);

		if( $this->driver->trim )
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






	protected function buildOrderSql()
	{
		$sql = '';
		if( !empty($this->orders) ) {
			$sql .= ' ORDER BY ';
			$parts = array();
			foreach( $this->orders as $order ) {
				list( $column , $ordering ) = $order;
				$parts[] = $this->driver->getQuoteColumn($column) . ' ' . $ordering;
			}
			$sql .= join(',',$parts);
		}
		return $sql;
	}

	protected function buildLimitSql()
	{
		$sql = '';
		if( $this->driver->type == 'postgresql' ) {
			if( $this->limit && $this->offset ) {
				$sql .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
			} else if ( $this->limit ) {
				$sql .= ' LIMIT ' . $this->limit;
			}
		} 
		else if( $this->driver->type == 'mysql' ) {
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
		if( $this->driver->placeholder ) {
			foreach( $this->update as $k => $v ) {
				if( is_array($v) ) {
					$conds[] =  $this->driver->getQuoteColumn( $k ) . ' = '. $v;
				} else {
					if( is_integer($k) )
						$k = $v;
					$conds[] =  $this->driver->getQuoteColumn($k) . ' = ' . $this->driver->getPlaceHolder($k);
				}
			}
		}
		else {
			foreach( $this->update as $k => $v ) {
				if( is_array($v) ) {
					$conds[] = $this->driver->getQuoteColumn($k) . ' = ' . $v ;
				} else {
					$conds[] = $this->driver->getQuoteColumn($k) . ' = ' 
                        . '\'' 
                        . $this->driver->escape($v)
                        . '\'';
				}
			}
		}
		return join(', ',$conds);
	}

	protected function buildConditionSql()
	{
        if( $this->where )
            return ' WHERE ' . $this->where->inflate();
        return '';
	}

}


