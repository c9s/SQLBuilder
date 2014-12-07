<?php
namespace SQLBuilder\Query;
use Exception;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Expr\SelectExpr;
use SQLBuilder\Syntax\Conditions;
use SQLBuilder\Syntax\Join;
use SQLBuilder\Syntax\IndexHint;
use SQLBuilder\Syntax\Paging;

/**
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $select = new SQLBuilder\Query\SelectQuery;
 *  $sqlbuilder->select(array(
 *      'foo',
 *      'bar',
 *  ));
 *  $sql = $select->toSql($driver, $args);
 *
 * @code
 *
 * The fluent interface rules of Query objects
 *
 *    1. setters should return self, since there is no return value.
 *    2. getters should be just what they are.
 *    3. modifier can set / append data and return self
 *
 */
class SelectQuery implements ToSqlInterface
{
    protected $select = array();

    protected $options = array();

    protected $from = array();

    protected $where;

    protected $having;

    protected $joins = array();

    protected $orderByList = array();

    protected $groupByList = array();

    protected $groupByModifiers = array();

    protected $indexHintOn = array();

    protected $paging;

    protected $modifiers = array();

    public function __construct()
    {
        $this->where = new Conditions;
        $this->having = new Conditions;
        $this->paging = new Paging;
    }


    /**********************************************************
     * Accessors
     **********************************************************/

    public function all() {
        return $this->option('ALL');
    }

    public function distinct() {
        return $this->option('DISTINCT');
    }

    public function distinctrow() {
        return $this->option('DISTINCTROW');
    }

    /********************************************************
     * MySQL Only Options
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/select.html
     *******************************************************/
    public function useSqlCache() {
        return $this->option('SQL_CACHE');
    }

    public function useSqlNoCache() {
        return $this->option('SQL_NO_CACHE');
    }

    public function useSmallResult() {
        return $this->option('SQL_SMALL_RESULT');
    }

    public function useBigResult() {
        return $this->option('SQL_BIG_RESULT');
    }

    public function useBufferResult() {
        return $this->option('SQL_BUFFER_RESULT');
    }

    /**
     * MySQL Select Options:
     *
     *   [ALL | DISTINCT | DISTINCTROW ]
     *   [HIGH_PRIORITY]
     *   [MAX_STATEMENT_TIME = N]
     *   [STRAIGHT_JOIN]
     *   [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     *   [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     *
     * $this->option([ 'SQL_SMALL_RESULT', 'SQL_CALC_FOUND_ROWS', 'MAX_STATEMENT_TIME = N']);
     */
    public function option($selectOption) 
    {
        if (is_array($selectOption)) {
            $this->options = $this->options + $selectOption;
        } else {
            $this->options = $this->options + func_get_args();
        }
        return $this;
    }

    public function options() {
        $this->options = func_get_args();
        return $this;
    }



    public function select($select) {
        if (is_array($select)) {
            $this->select = $this->select + $select;
        } else {
            $this->select = $this->select + func_get_args();
        }
        return $this;
    }

    public function setSelect($selecct) {
        if (is_array($select)) {
            $this->select = $select;
        } else {
            $this->select = func_get_args();
        }
        return $this;
    }

    public function getSelect() {
        return $this->select;
    }

    /**
     * ->from('posts', 'p')
     * ->from('users', 'u')
     */
    public function from($table, $alias = NULL) {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }
        return $this;
    }

    public function setFrom($table) {
        if (is_array($table)) {
            $this->from = $table;
        } else {
            $this->from = func_get_args();
        }
        return $this;
    }

    public function getFrom() {
        return $this->from;
    }

    public function join($table, $alias = NULL) {
        $join = new Join($table, $alias);
        $this->joins[] = $join;
        return $join;
    }

    public function getJoins() {
        return $this->joins;
    }

    public function getLastJoin() {
        return end($this->joins);
    }

    public function indexHintOn($tableRef) {
        $hint = new IndexHint;
        $this->indexHintOn[$tableRef] = $hint;
        return $hint;
    }

    public function where($expr = NULL , array $args = array()) {

        if (is_string($expr)) {
            $this->where->appendExpr($expr, $args);
        } elseif (!is_null($expr)) {
            throw new LogicException("Unsupported argument type of 'where' method.");
        }
        return $this->where;
    }


    public function having($expr = NULL , array $args = array()) {

        if (is_string($expr)) {
            $this->having->appendExpr($expr, $args);
        } else {
            throw new LogicException("Unsupported argument type of 'where' method.");
        }
        return $this->having;
    }


    /**
    The Syntax:

    [ORDER BY {col_name | expr | position}
        [ASC | DESC], ...]

    > SELECT * FROM foo ORDER BY RAND(NOW()) LIMIT 1;
    > SELECT * FROM foo ORDER BY 1,2,3;

    > SELECT* FROM mytable ORDER BY
        LOCATE(CONCAT('.',`group`,'.'),'.9.7.6.10.8.5.');

    > SELECT `names`, `group`
        FROM my_table
        WHERE `group` IN (9,7,6,10,8,5)
        ORDER BY find_in_set(`group`,'9,7,6,10,8,5');

    @see http://dba.stackexchange.com/questions/5422/mysql-conditional-order-by-to-only-one-column
    @see http://dev.mysql.com/doc/refman/5.1/en/sorting-rows.html
    */
    public function orderBy($byExpr, $sorting = NULL) {
        if ($sorting) {
            $this->orderByList[] = array($byExpr, $sorting);
        } else {
            $this->orderByList[] = array($byExpr);
        }
        return $this;
    }

    public function clearOrderBy() { 
        $this->orderByList = array();
    }

    public function setOrderBy(array $orderBy) {
        $this->orderByList = $orderBy;
        return $this;
    }




    /********************************************************
     * LIMIT and OFFSET clauses
     *
     *******************************************************/
    public function limit($limit)
    {
        $this->paging->limit($limit);
        return $this;
    }

    public function offset($offset)
    {
        $this->paging->offset($offset);
        return $this;
    }

    public function page($page, $pageSize = 10)
    {
        $this->paging->page($page, $pageSize);
        return $this;
    }


    /**
     * Functions support GROUP BY
     *
     *  > SELECT FROM_DAYS(SUM(TO_DAYS(date_col))) FROM tbl_name;
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/group-by-functions.html
     *
     *
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/group-by-functions-and-modifiers.html
     */
    public function groupBy($expr, array $modifiers = array())
    {
        $this->groupByList[] = $expr;
        if (!empty($modifiers)) {
            $this->groupByModifiers = $modifiers;
        }
        return $this;
    }

    /**
     * Note: SELECT FOR UPDATE does not work when used in select statement with a subquery.
     */
    public function forUpdate() {
        $this->modifiers[] = 'FOR UPDATE';
        return $this;
    }


    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildOptionClause() 
    {
        if (empty($this->options)) {
            return '';
        }
        return ' ' . join(' ', $this->options);
    }

    public function buildSelectClause(BaseDriver $driver, ArgumentArray $args) {
        $cols = array();
        foreach($this->select as $k => $v) {
            if ($v instanceof SelectExpr) {
                $cols[] = $v->toSql($driver, $args);
            }
            /* "column AS alias" OR just "column" */
            elseif (is_string($k))
            {
                $cols[] = $driver->quoteColumn($k) . ' AS ' . $v;
            }
            elseif (is_integer($k) || is_numeric($k)) 
            {
                $cols[] = $driver->quoteColumn($v);
            }
        }
        return ' ' . join(', ',$cols);
    }

    public function buildIndexHintClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->indexHintOn)) {
            return '';
        }
        $clauses = array();
        foreach($this->indexHintOn as $hint) {
            $clauses[] = $hint->toSql($driver, $args);
        }
        return ' ' . join(' ', $clauses);
    }

    public function buildFromClause(BaseDriver $driver) {
        $tableRefs = array();
        foreach($this->from as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTableName($k) . ' AS ' . $v;
                if ($driver instanceof MySQLDriver && isset($this->indexHintOn[$k])) {
                    $sql .= $this->indexHintOn[$k]->toSql($driver, new ArgumentArray);
                }
                $tableRefs[] = $sql;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $sql = $driver->quoteTableName($v);
                if ($driver instanceof MySQLDriver && isset($this->indexHintOn[$v])) {
                    $sql .= $this->indexHintOn[$v]->toSql($driver, NULL);
                }
                $tableRefs[] = $sql;
            }
        }
        if (!empty($tableRefs)) {
            return ' FROM ' . join(', ', $tableRefs);
        }
        return '';
    }

    public function buildJoinClause(BaseDriver $driver, ArgumentArray $args) {
        $sql = '';
        if (!empty($this->joins)) {
            foreach($this->joins as $join) {
                $sql .= $join->toSql($driver, $args);
            }
        }
        return $sql;
    }

    public function buildGroupByClause(BaseDriver $driver, ArgumentArray $args) {
        if (empty($this->groupByList)) {
            return '';
        }
        $clauses = array();
        foreach($this->groupByList as $groupBy) {
            if (is_string($groupBy)) {
                $clauses[] = $groupBy;
            } elseif ($groupBy instanceof ToSqlInterface) {
                $clauses[] = $groupBy->toSql($driver, $args);
            } else {
                throw new InvalidArgumentException('Unsupported variable type for GROUP BY clause');
            }
        }
        // TODO: group by modifiers, currently only support for syntax like "GROUP BY a WITH ROLLUP".
        // @see http://dev.mysql.com/doc/refman/5.7/en/group-by-modifiers.html
        $sql = ' GROUP BY ' . join(', ', $clauses);
        if ($this->groupByModifiers) {
            $sql .= ' ' . join(' ', $this->groupByModifiers);
        }
        return $sql;
    }

    public function buildOrderByClause(BaseDriver $driver, ArgumentArray $args) {
        if (empty($this->orderByList)) {
            return '';
        }
        $clauses = array();
        foreach($this->orderByList as $orderBy) {
            if (count($orderBy) === 1) {
                $clauses[] = $orderBy[0];
            } elseif (count($orderBy) === 2) {
                $clauses[] = $orderBy[0] . ' ' . strtoupper($orderBy[1]);
            } elseif ($orderBy instanceof ToSqlInterface) {
                $clauses[] = $orderBy->toSql($driver, $args);
            }
        }
        return ' ORDER BY ' . join(', ', $clauses);
    }

    public function buildLimitClause(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->paging->toSql($driver, $args);
    }


    public function buildModifierClause()
    {
        if (empty($this->modifiers)) {
            return '';
        }
        return ' ' . join(' ', $this->modifiers);
    }

    public function buildWhereClause(BaseDriver $driver, ArgumentArray $args) {
        if ($this->where->hasExprs()) {
            return ' WHERE ' . $this->where->toSql($driver, $args);
        }
        return '';
    }

    public function buildHavingClause(BaseDriver $driver, ArgumentArray $args) {
        if ($this->having->hasExprs()) {
            return ' HAVING ' . $this->having->toSql($driver, $args);
        }
        return '';
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'SELECT'
            . $this->buildOptionClause()
            . $this->buildSelectClause($driver, $args)
            . $this->buildFromClause($driver)
            . $this->buildIndexHintClause($driver, $args)
            . $this->buildJoinClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildGroupByClause($driver, $args)
            . $this->buildHavingClause($driver, $args)
            . $this->buildOrderByClause($driver, $args)
            . $this->buildLimitClause($driver, $args)
            . $this->buildModifierClause()
            ;
        return $sql;
    }
}

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
     * args: column to value 
     */
    public function insert(array $args)
    {
        $this->insert = $args;
        $this->behavior = static::INSERT;
        return $this;
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
        if (null === $args || empty($args)) {
            return $this;
        }

        $expr = $this->where();
        foreach( $args as $k => $v ) {
            $expr = $expr->equal( $k , $v );
        }
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
            return $this->buildUpdateSql();
            break;
        case static::INSERT:
            return $this->buildInsertSql();
            break;
        case static::DELETE:
            return $this->buildDeleteSql();
            break;
        case static::SELECT:
            return $this->buildSelectSql();
            break;
        default:
            throw new Exception('behavior is not defined.');
            break;
        }
    }

    public function buildTableAliasSql()
    {
        if ($this->alias) {
            return ' ' . $this->alias;
        }
        return '';
    }



    /**
     * builder
     */
    public function buildSelectColumnSql()
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

    public function buildDeleteSql()
    {
        $sql = 'DELETE FROM ' . $this->driver->quoteTableName($this->table);
        $sql .= $this->buildConditionSql();

        /* only supported in mysql, sqlite */
        if ( $this->driver instanceof MySQLDriver
            || $this->driver instanceof SQLiteDriver ) {
            $sql .= $this->buildLimitSql();
        }
        return $sql;
    }


    public function buildUpdateSql()
    {
        // Do not build with table alias for SQLite, because SQLite does not support it.
        $sql = 'UPDATE ' . $this->driver->quoteTableName($this->table)
            . ( ! $this->driver instanceof SQLiteDriver ? $this->buildTableAliasSql() : '' )
            . ' SET '
            . $this->buildSetterSql()
            . $this->buildJoinSql()
            . $this->buildConditionSql()
            ;

        /* the LIMIT statement in Update clause is only supported in mysql, sqlite */
        if ($this->driver instanceof MySQLDriver || $this->driver instanceof SQLiteDriver) {
            $sql .= $this->buildLimitSql();
        }
        return $sql;
    }


    /** 
     * build select sql
     */
    public function buildSelectSql()
    {
        /* check required arguments */
        $sql = 'SELECT ' 
            . $this->buildSelectColumnSql()
            . ' FROM ' 
            . $this->driver->quoteTableName($this->table)
            . $this->buildTableAliasSql() 
            . $this->buildJoinSql()
            . $this->buildConditionSql()
            . $this->buildGroupBySql()
            . $this->buildHavingSql()
            . $this->buildOrderSql()
            . $this->buildLimitSql()
            ;
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
                    $conds[] = $this->driver->quoteColumn($k) . ' = ' . $this->driver->deflate($v);
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

