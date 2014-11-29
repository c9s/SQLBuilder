<?php
namespace SQLBuilder;
use Exception;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;

/**
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $sqlbuilder = new SQLBuilder\QueryBuilder($driver);
 *
 *  $sqlbuilder->insert(array(
 *      'foo' => 'foo',
 *      'bar' => 'bar',
 *  ));
 *
 *  $sqlbuilder->insert(array(
 *      'foo',
 *      'bar',
 *  ));
 *
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




    public $groupBys = array();

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

    public $having;

    public $orders = array();

    /**
     * selected columns
     *
     * @var string[] an array contains column names
     */
    public $selected = array('*');


    /**
     * Arguments for insert statement.
     *
     * @var array
     */
    public $insert;


    /**
     * Arguments for update statement.
     *
     * @var array
     */
    public $update;


    /**
     * Behavior
     */
    public $behavior = 4; // default to static::SELECT

    public $vars = array();

    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;
    const SELECT = 4;


    /**
     * @param Driver $driver object
     * @param string $table table name
     */
    public function __construct(BaseDriver $driver, $table = NULL)
    {
        $this->driver = $driver;
        if ($table) {
            $this->table = $table;
        }
    }

    /**
     * set table name
     *
     * @param string $table table name
     */
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

    public function addSelect($columns) {
        $args = func_get_args();
        $columns = $args[0];
        if ( is_array($columns) ) {
            $this->selected = array_merge( $this->selected , $columns );
        } else {
            $this->selected = array_merge( $this->selected , $args );
        }
        $this->behavior = static::SELECT;
        return $this;
    }

    /**
     * Select behavior
     *
     * @param array|string
     *
     *    ->select('column1','column2')
     *    ->select(array('column1','column2'))
     *    ->select(array('column1' => 'new_name'))
     */
    public function select($columns)
    {
        $args = func_get_args();
        $columns = $args[0];
        if ( is_array($columns) ) {
            $this->selected = $columns;
        } else {
            $this->selected = $args;
        }
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
        return $this;
    }


    /*** limit , offset methods ***/
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }


    /**
     * setup offset syntax
     *
     * @param integer $offset
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }



    /**
     * setup table alias
     *
     * @param string $alias table alias
     */
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }


    /**
     * join table
     *
     * @param string $table table name
     * @param string $type  join type, valid types are: 'left', 'right', 'inner' ..
     *
     * @return SQLBuilder\JoinExpression
     */
    public function join($table,$type = 'LEFT')
    {
        $this->joinExpr[] = $expr = new JoinExpression($table,$type);
        $expr->driver = $this->driver;
        $expr->parent = $this;
        $expr->builder = $this;
        return $expr;
    }

    /*** condition methods ***/


    /**
     * setup where condition
     *
     * @return SQLBuilder\Expression
     */
    public function where( $args = null )
    {
        if ( $args && is_array($args) ) {
            return $this->whereFromArgs( $args );
        }

        if ( $this->where ) {
            return $this->where;
        }

        $this->where = $expr = new Expression;
        $expr->driver = $this->driver;
        $expr->parent = $this;
        $expr->builder = $this;
        return $expr;
    }


    /**
     * build expressions from arguments for simple usage.
     *
     * @param array $args
     *
     * @return SQLBuilder\QueryBuilder
     */
    public function whereFromArgs($args)
    {
        if ( null === $args || empty($args) )
            return $this;

        $expr = $this->where();
        foreach( $args as $k => $v ) {
            $expr = $expr->equal( $k , $v );
        }
        return $this;
    }


    /**
     * set returning column data when inserting data
     *
     * postgresql-only 
     *
     * @param string $column column name
     * */
    public function returning($column)
    {
        $this->returning = $column;
        return $this;
    }


    /**
     * push order 
     *
     * @param string $column column name
     * @param string $order  order type, desc or asc
     */
    public function order($column,$order = 'desc')
    {
        $this->orders[] = array( $column , $order );
        return $this;
    }


    // alias method
    public function orderBy($column,$order = 'desc')
    {
        $this->orders[] = array( $column , $order );
        return $this;
    }


    /**
     * group by column
     *
     * @param string $column column name
     */
    public function groupBy($column)
    {
        $args = func_get_args();
        if ( count($args) > 1 ) {
            $this->groupBys = $args;
        } else {
            $this->groupBys[] = $column;
        }
        return $this;
    }

    public function clearGroupBy() {
        $this->groupBys = array();
        return $this;
    }

    public function clearOrderBy() {
        $this->orders = array();
        return $this;
    }


    /**
     * to support syntax like:
     *    GROUP BY product_id, p.name, p.price, p.cost
     * HAVING sum(p.price * s.units) > 5000;
     */
    public function having()
    {
        $this->having = $expr = new Expression;
        $expr->driver = $this->driver;
        $expr->builder = $this;
        $expr->parent = $this;
        return $expr;
    }

    /*************************
     * public interface 
     *************************/


    public function build()
    {
        // reset sql vars (for applying SQL statement)
        $this->vars = array();
        if ( ! $this->behavior )
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

    public function getTableAliasSql()
    {
        if ($this->alias) {
            return ' ' . $this->alias;
        }
        return '';
    }



    /**
     * builder
     */
    public function buildSelectColumns()
    {
        $cols = array();
        foreach( $this->selected as $k => $v ) {

            /* column => alias */
            if (is_string($k)) {
                $cols[] = $this->driver->quoteColumn($k) . ' AS ' . $v;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $cols[] = $this->driver->quoteColumn($v);
            }
        }
        return join(', ',$cols);
    }

    public function buildDelete()
    {
        $sql = 'DELETE FROM ' . $this->driver->quoteTableName($this->table);
        $sql .= $this->buildConditionSql();

        /* only supported in mysql, sqlite */
        if ( $this->driver instanceof MySQLDriver
            || $this->driver instanceof SQLiteDriver ) {
            $sql .= $this->buildLimitSql();
        }

        if ( $this->driver->trim ) {
            return trim($sql);
        }
        return $sql;
    }


    public function buildUpdate()
    {
        // Do not build with table alias for SQLite, because SQLite does not support it.
        $sql = 'UPDATE ' . $this->driver->quoteTableName($this->table)
            . ( ! $this->driver instanceof SQLiteDriver ? $this->getTableAliasSql() : '' )
            . ' SET '
            . $this->buildSetterSql()
            . $this->buildJoinSql()
            . $this->buildConditionSql()
            ;

        /* only supported in mysql, sqlite */
        if ($this->driver instanceof MySQLDriver || $this->driver instanceof SQLiteDriver) {
            $sql .= $this->buildLimitSql();
        }

        if ( $this->driver->trim ) {
            return trim($sql);
        }
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
            . ' FROM ' 
            . $this->driver->quoteTableName($this->table)
            . $this->getTableAliasSql() 
            . $this->buildJoinSql()
            . $this->buildConditionSql()
            . $this->buildGroupBySql()
            . $this->buildHavingSql()
            . $this->buildOrderSql()
            . $this->buildLimitSql()
            ;

        if ( $this->driver->trim ) {
            return trim($sql);
        }
        return $sql;
    }




    public function buildInsert()
    {
        /* check required arguments */
        $columns = array();
        $values = array();

        /* build sql arguments */

        if ( $this->driver->paramMarker ) {
            foreach( $this->insert as $k => $v ) {
                if (is_integer($k))
                    $k = $v;
                if (is_array($v)) {
                    // just interpolate the raw value
                    $columns[] = $this->driver->quoteColumn($k);
                    $values[] = $v[0];
                } elseif ($v instanceof RawValue) {
                    $columns[] = $this->driver->quoteColumn($k);
                    $values[] = $v;
                } else {
                    $columns[] = $this->driver->quoteColumn($k);
                    $newK = $this->setPlaceHolderVar( $k , $v );
                    $values[] = $this->driver->getParamMarker($newK);
                }
            }

        } else {
            foreach( $this->insert as $k => $v ) {
                if (is_integer($k)) {
                    $k = $v;
                }
                $columns[] = $this->driver->quoteColumn( $k );
                $values[]  = $this->driver->inflate($v);
            }
        }

        $sql = 'INSERT INTO ' . $this->driver->quoteTableName($this->table)
            . ' ( '
            . join(',',$columns) 
            . ') VALUES (' 
            . join(',', $values ) 
            . ')';
            ;

        if ($this->returning && ($this->driver instanceof PgSQLDriver) ) {
            $sql .= ' RETURNING ' . $this->driver->quoteColumn($this->returning);
        }

        if ( $this->driver->trim ) {
            return trim($sql);
        }
        return $sql;
    }

    public function buildJoinSql()
    {
        $sql = '';
        foreach( $this->joinExpr as $expr ) {
            $sql .= $expr->toSql();
        }
        return $sql;
    }

    public function buildOrderSql()
    {
        $sql = '';
        if ( !empty($this->orders) ) {
            $sql .= ' ORDER BY ';
            $parts = array();
            foreach( $this->orders as $order ) {
                list( $column , $ordering ) = $order;
                $parts[] = $this->driver->quoteColumn($column) . ' ' . $ordering;
            }
            $sql .= join(',',$parts);
        }
        return $sql;
    }

    public function buildLimitSql()
    {
        $sql = '';
        if ( $this->driver instanceof PgSQLDriver ) {
            if ( $this->limit && $this->offset ) {
                $sql .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
            } elseif ( $this->limit ) {
                $sql .= ' LIMIT ' . $this->limit;
            }
        } elseif ( $this->driver instanceof MySQLDriver ) {
            if ( $this->limit && $this->offset ) {
                $sql .= ' LIMIT ' . $this->offset . ' , ' . $this->limit;
            } elseif ( $this->limit ) {
                $sql .= ' LIMIT ' . $this->limit;
            }
        } elseif ($this->driver instanceof SQLiteDriver) {
            // just ignore
        }
        return $sql;
    }

    public function buildGroupBySql()
    {
        $self = $this;
        if ( ! empty($this->groupBys) ) {
            return ' GROUP BY ' . join( ',' , 
                array_map( function($val) use ($self) { 
                    return $self->driver->quoteColumn( $val );
                } , $this->groupBys )
            );
        }
    }

    public function buildSetterSql()
    {
        $conds = array();
        if ($this->driver->paramMarker) {
            foreach( $this->update as $k => $v ) {
                if (is_array($v)) {
                    $conds[] =  $this->driver->quoteColumn( $k ) . ' = '. $v[0];
                } elseif ($v instanceof RawValue) {
                    $conds[] =  $this->driver->quoteColumn( $k ) . ' = '. $v->__toString();
                } else {
                    if (is_integer($k))
                        $k = $v;
                    $newK = $this->setPlaceHolderVar( $k , $v );
                    $conds[] =  $this->driver->quoteColumn($k) . ' = ' . $this->driver->getParamMarker($newK);
                }
            }
        }
        else {
            foreach( $this->update as $k => $v ) {
                if (is_array($v)) {
                    $conds[] = $this->driver->quoteColumn($k) . ' = ' . $v[0];
                } elseif ($v instanceof RawValue) {
                    $conds[] = $this->driver->quoteColumn($k) . ' = ' . $v->__toString();
                } else {
                    $conds[] = $this->driver->quoteColumn($k) . ' = ' . $this->driver->inflate($v);
                }
            }
        }
        return join(', ',$conds);
    }

    public function buildConditionSql()
    {
        if ($this->where && $this->where->isComplete()) {
            return ' WHERE ' . $this->where->toSql();
        }
        return '';
    }

    public function buildHavingSql()
    {
        if ($this->having ) {
            return ' HAVING ' . $this->having->toSql();
        }
        return '';
    }


    public function getVars()
    {
        return $this->vars;
    }


    /**
     * Save varaible for SQL statement, 
     * returns new variable name if the variable name is already defined.
     *
     *
     */
    public function setPlaceHolderVar($key,$value)
    {
        if ( $this->driver->paramMarker && $this->driver->paramMarker === BaseDriver::NAMED_PARAM_MARKER ) {
            $key = preg_replace('#\W+#','_', $key );
            // a basic counter to avoid key confliction.
            $i = 1;
            while( isset($this->vars[':' . $key]) ) {
                $key .= $i++;
            }
            $this->vars[ ':' . $key  ] = PDOParameter::cast($value);
            return $key;
        } else {
            $this->vars[] = $value;
            return $key;
        }
    }

    public function __clone()
    {
        if ( $this->where ) {
            // after clone, set new builder object to self.
            $this->where = clone $this->where;
            $this->where->setBuilder($this);
        }
        if ( $this->joinExpr ) {
            $nodes = array();
            foreach( $this->joinExpr as $expr ) {
                $n = clone $expr;
                $n->builder = $this;
                $nodes[] = $n;
            }
            $this->joinExpr = $nodes;
        }
    }

}

