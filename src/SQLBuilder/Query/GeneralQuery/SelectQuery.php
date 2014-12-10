<?php
namespace SQLBuilder\Query\GeneralQuery;
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
use SQLBuilder\Syntax\Partition;
use SQLBuilder\Traits\OrderByTrait;
use SQLBuilder\Traits\WhereTrait;
use SQLBuilder\Traits\PartitionTrait;
use SQLBuilder\Traits\JoinTrait;
use SQLBuilder\Traits\OptionTrait;


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
    use OrderByTrait;
    use WhereTrait;
    use JoinTrait;
    use PartitionTrait;
    use OptionTrait;

    protected $select = array();

    protected $options = array();

    protected $from = array();

    protected $having;


    protected $groupByList = array();

    protected $groupByModifiers = array();

    protected $paging;

    protected $lockModifier;

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


    public function having($expr = NULL , array $args = array()) {

        if (is_string($expr)) {
            $this->having->appendExpr($expr, $args);
        } else {
            throw new LogicException("Unsupported argument type of 'where' method.");
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
        $this->lockModifier = 'FOR UPDATE';
        return $this;
    }

    public function lockInShareMode() {
        $this->lockModifier = 'LOCK IN SHARE MODE';
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

            if (is_integer($k)) {
                if ($v instanceof SelectExpr) {
                    $sql .= $v->toSql($driver, $args);
                } else {
                    $sql .= $driver->quoteColumn($v);
                }
            } else {
                /* "column AS alias" OR just "column" */
                $sql .= $driver->quoteColumn($k) . ' AS ' . $v;
            }
        }
        return $sql;
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
                    $sql .= $this->indexHintOn[$v]->toSql($driver, new ArgumentArray);
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
            . $this->buildFromClause($driver)
            . $this->buildPartitionClause($driver, $args)
            . $this->buildJoinClause($driver, $args)
            . $this->buildJoinIndexHintClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildGroupByClause($driver, $args)
            . $this->buildHavingClause($driver, $args)
            . $this->buildOrderByClause($driver, $args)
            . $this->buildLimitClause($driver, $args)
            . $this->buildLockModifierClause()
            ;
        return $sql;
    }
}

