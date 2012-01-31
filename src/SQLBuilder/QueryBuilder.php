<?php
namespace SQLBuilder;
use Exception;

/**
 *
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $sqlbuilder = new SQLBuilder\QueryBuilder();
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
class QueryBuilder 
{
    /**
     * table name 
     *
     * @var string
     * */
    public $table;


    /**
     * table alias
     */
    public $alias;

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


    public $joinExpr = array();

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
    public function __construct($table = null)
    {
        $this->table = $table;
        $this->selected = array('*');
        $this->behavior = static::SELECT;
    }



    public function table($table)
    {
        $this->table = $table;
        return $this;
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
        $columns = func_get_args();
        if( is_array($columns[0]) )
            $this->selected = $columns[0];
        else
            $this->selected = $columns;
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


    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }


    public function join($table,$type = 'LEFT')
    {
        $this->joinExpr[] = $expr = new JoinExpression($table,$type);
        $expr->driver = $this->driver;
        $expr->parent = $this;
        return $expr;
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
            $expr = $expr->equal( $k , $v );
        }
        return $this;
    }


    /* for postgresql-only */
    public function returning($column)
    {
        $this->returning = $column;
        return $this;
    }


    public function order($column,$order = 'desc')
    {
        $this->orders[] = array( $column , $order );
        return $this;
    }



    /*************************
     * public interface 
     *************************/


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




    /**
     * get table name (with quote or not)
     *
     * quotes can be used in postgresql:
     *     select * from "table_name";
     */
    protected function getTableSql()
    {
        $sql = '';
        if( $this->driver->quoteTable ) {
            $sql = '"' . $this->table . '"';
        } else {
            $sql = $this->table;
        }

        if( $this->alias )
            $sql .= ' ' . $this->alias;
        return $sql;
    }



    /**
     * builder, protected methods
     */
    protected function buildSelectColumns()
    {
        $cols = array();
        foreach( $this->selected as $k => $v ) {

            /* column => alias */
            if( is_string($k) ) {
                $cols[] = $this->driver->getQuoteColumn($k) . '  AS ' . $v;
            }
            elseif( is_integer($k) ) {
                $cols[] = $this->driver->getQuoteColumn($v);
            }
        }
        return join(', ',$cols);
    }

    protected function buildDelete()
    {
        $sql = 'DELETE FROM ' . $this->getTableSql() . ' ';
        $sql .= $this->buildConditionSql();
        $sql .= $this->buildLimitSql();
        if( $this->driver->trim )
            return trim($sql);
        return $sql;
    }


    protected function buildUpdate()
    {
        $sql = 'UPDATE ' . $this->getTableSql() . ' SET ';

        $sql .= $this->buildSetterSql();

        $sql .= $this->buildJoinSql();

        $sql .= $this->buildConditionSql();

        $sql .= $this->buildLimitSql();
        if( $this->driver->trim )
            return trim($sql);
        return $sql;
    }


    /** 
     * build select sql
     */
    protected function buildSelect()
    {
        /* check required arguments */
        $sql = 'SELECT ' 
            . $this->buildSelectColumns()
            . ' FROM ' . $this->getTableSql() . ' ';

        $sql .= $this->buildJoinSql();

        $sql .= $this->buildConditionSql();

        $sql .= $this->buildOrderSql();

        $sql .= $this->buildLimitSql();

        if( $this->driver->trim )
            return trim($sql);
        return $sql;
    }




    protected function buildInsert()
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
                $values[]  = $this->driver->escape($v);

            }
        }

        $sql = ' INSERT INTO ' . $this->getTableSql() . ' ( ';
        $sql .= join(',',$columns) . ") VALUES (".  join(',', $values ) .")";

        if( $this->returning )
            $sql .= ' RETURNING ' . $this->driver->getQuoteColumn($this->returning);

        if( $this->driver->trim )
            return trim($sql);
        return $sql;
    }



    protected function buildJoinSql()
    {
        $sql = '';
        foreach( $this->joinExpr as $expr ) {
            $sql .= $expr->toSql();
        }
        return $sql;
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
                        . $this->driver->escape($v);
                }
            }
        }
        return join(', ',$conds);
    }

    protected function buildConditionSql()
    {
        if( $this->where )
            return ' WHERE ' . $this->where->toSql();
        return '';
    }

}


