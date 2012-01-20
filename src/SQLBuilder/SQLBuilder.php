<?php
namespace SQLBuilder;
use Exception;

class SQLBuilder 
{
	public $table;
	public $limit;
	public $offset;
	public $returning;
	public $where = array();
	public $orders = array();

	public $selected;
	public $insert;
	public $update;
	public $behavior;

	const insert = 1;
	const update = 2;
	const delete = 3;
	const select = 4;

	protected $driver = 'PDO';
	protected $quoteTable = true;
	protected $quoteColumn = true;
	protected $trim = false;
	protected $placeholder = false;
	protected $escaper;

	public function __construct($table)
	{
		$this->table = $table;
		$this->selected = array('*');
		$this->behavior = self::select;


		/**
		 * mysqli_real_escape_string:
		 *
		 *    string mysqli_real_escape_string ( mysqli $link , string $escapestr )
	     *    string pg_escape_string ([ resource $connection ], string $data )
		 *    string PDO::quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
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


	public function getPlaceHolder($key)
	{
		if( $this->placeholder && $this->placeholder === 'named' ) {
			return ':' . $key;
		}
		else {
			return '?';
		}
	}

	public function update($args)
	{
		$this->update = $args;
		$this->behavior = self::update;
	}


	public function select($columns)
	{
		$this->selected = (array) $columns;
		$this->behavior = self::select;
	}

	/**
	 * args: column to value 
	 */
	public function insert(array $args)
	{
		$this->insert = $args;
		$this->behavior = self::insert;
	}

	public function delete()
	{
		$this->behavior = self::delete;
	}


	public function limit($limit)
	{
		$this->limit = $limit;
	}

	public function offset($offset)
	{
		$this->offset = $offset;
	}



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





	/* builder, protected methods */
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
		$sql = "DELETE FROM \"{$this->table}\" ";
		$sql .= $this->buildConditionSql();
		$sql .= $this->buildLimitSql();
		if( $this->trim )
			return trim($sql);
        return $sql;
    }


	public function buildUpdate()
	{
		$sql = "UPDATE \"{$this->table}\" SET ";
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
		$sql = "SELECT " 
			. $this->buildSelectColumns()
			. " FROM \"{$this->table}\" ";

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
				$columns[] = $this->quoteColumn($k);
				$values[] = $this->getPlaceHolder($k);
			}

		} else {
			foreach( $this->insert as $k => $v ) {
				if( is_integer($k) )
					$k = $v;
				$columns[] = $this->quoteColumn( $k );
				$values[]  = '\'' . call_user_func( $this->escaper , $v ) . '\'';
			}
		}

        $sql = " INSERT INTO \"{$this->table}\" ( ";
        $sql .= join(',',$columns) . ") VALUES (".  join(',', $values ) .")";

		if( $this->returning )
			$sql .= ' RETURNING ' . $this->quoteColumn($this->returning);

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
			case self::update:
				return $this->buildUpdate();
				break;
			case self::insert:
				return $this->buildInsert();
				break;
			case self::delete:
				return $this->buildDelete();
				break;
			case self::select:
				return $this->buildSelect();
				break;
			default:
				throw new Exception('behavior is not defined.');
				break;
		}
	}





	protected function quoteColumn($name)
	{
		if( $this->quoteColumn ) {
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
				$parts[] = $this->quoteColumn($column) . ' ' . $ordering;
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

