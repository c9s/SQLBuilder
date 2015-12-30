<?php
namespace SQLBuilder\Universal\Query;
use Exception;
use LogicException;
use InvalidArgumentException;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Syntax\Join;
use SQLBuilder\Universal\Syntax\IndexHint;
use SQLBuilder\Universal\Syntax\Paging;
use SQLBuilder\MySQL\Syntax\Partition;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\Universal\Expr\SelectExpr;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\MySQL\Traits\IndexHintTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;


/**
 * SQL Builder for generating CRUD SQL
 *
 * @code
 *
 *  $select = new SQLBuilder\Universal\Query\SelectQuery;
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
    use OrderByTrait;
    use WhereTrait;
    use JoinTrait;
    use PartitionTrait;
    use OptionTrait;
    use IndexHintTrait;

    protected $select = array();

    protected $from = array();

    protected $having;

    protected $groupByList = array();

    protected $groupByModifiers = array();

    protected $paging;

    protected $lockModifier;

    protected $rollupModifier;

    public function __construct()
    {
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

    public function distinctRow() {
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

    public function select($select) {
        if (is_array($select)) {
            $this->select = $this->select + $select;
        } else {
            $this->select = $this->select + func_get_args();
        }
        return $this;
    }

    public function setSelect($select) {
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


    public function having($expr = NULL , array $args = array()) {
        if (is_string($expr)) {
            $this->having->appendExpr($expr, $args);
        }
        return $this->having;
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
        if (is_array($expr)) {
            $this->groupByList = array_merge($this->groupByList,$expr);
        } else {
            $this->groupByList[] = $expr;
        }
        if (!empty($modifiers)) {
            $this->groupByModifiers = $modifiers;
        }
        return $this;
    }

    public function clearGroupBy()
    {
        $this->groupByList = array();
    }


    /**
     * Note: SELECT FOR UPDATE does not work when used in select statement with a subquery.
     */
    public function forUpdate()
    {
        $this->lockModifier = 'FOR UPDATE';
        return $this;
    }

    public function lockInShareMode()
    {
        $this->lockModifier = 'LOCK IN SHARE MODE';
    }

    public function rollup()
    {
        $this->rollupModifier = 'WITH ROLLUP';
    }

    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildSelectClause(BaseDriver $driver, ArgumentArray $args) {
        $sql = ' ';
        $cols = array();
        $first = true;
        foreach($this->select as $k => $v) {
            if ($first) {
                $first = false;
            } else {
                $sql .= ', ';
            }

            /* "column AS alias" OR just "column" */
            if (is_integer($k)) {
                if ($v instanceof SelectExpr || $v instanceof ToSqlInterface) {
                    $sql .= $v->toSql($driver, $args);
                } else if (is_array($v)) {
                    if (count($v) == 2) {
                        $sql .= $v[0] . ' AS ' . $v[1];
                    }  else {
                        $sql .= $v[0];
                    }
                } else {
                    $sql .= $driver->quoteColumn($v);
                }
            } else {
                $sql .= $driver->quoteColumn($k) . ' AS ' . $v;
            }
        }
        return $sql;
    }


    public function buildFromClause(BaseDriver $driver, ArgumentArray $args) {
        $tableRefs = array();
        foreach($this->from as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $v;

                if ($driver instanceof MySQLDriver) {
                    if ($this->definedIndexHint($v)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($v, $driver, $args);
                    } elseif ($this->definedIndexHint($k)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($k, $driver, $args);
                    }
                }
                $tableRefs[] = $sql;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $sql = $driver->quoteTable($v);
                if ($driver instanceof MySQLDriver && $this->definedIndexHint($v)) {
                    $sql .= $this->buildIndexHintClauseByTableRef($v, $driver, $args);
                }
                $tableRefs[] = $sql;
            }
        }
        if (!empty($tableRefs)) {
            return ' FROM ' . join(', ', $tableRefs);
        }
        return '';
    }




    public function buildGroupByClause(BaseDriver $driver, ArgumentArray $args) {
        if (empty($this->groupByList)) {
            return '';
        }
        $clauses = array();
        foreach($this->groupByList as $groupBy) {
            if (is_string($groupBy)) {
                $clauses[] = $groupBy;
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

        if ($this->rollupModifier) {
            if (!$driver instanceof MySQLDriver) {
                throw new Exception("Incompatible Query Usage: rollup is only supported in MySQL.");
            }
            $sql .= ' ' . $this->rollupModifier;
        }
        return $sql;
    }


    public function buildLimitClause(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->paging->toSql($driver, $args);
    }


    public function buildLockModifierClause()
    {
        if ($this->lockModifier) {
            return ' ' . $this->lockModifier;
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
            . $this->buildFromClause($driver, $args)
            . $this->buildPartitionClause($driver, $args)
            . $this->buildJoinClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildGroupByClause($driver, $args)
            . $this->buildHavingClause($driver, $args)
            . $this->buildOrderByClause($driver, $args)
            . $this->buildLimitClause($driver, $args)
            . $this->buildLockModifierClause()
            ;
        return $sql;
    }

    public function __clone() {
        $this->having = $this->having;
        $this->paging = $this->paging;
        $this->where = $this->where;
    }
}

