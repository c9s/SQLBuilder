<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\MySQL\Traits\IndexHintTrait;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Expr\SelectExpr;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\PagingTrait;
use SQLBuilder\Universal\Traits\WhereTrait;

/**
 * Class SelectQuery
 *
 * SQL Builder for generating CRUD SQL.
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
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class SelectQuery implements ToSqlInterface
{
    use OrderByTrait;
    use WhereTrait;
    use JoinTrait;
    use PartitionTrait;
    use OptionTrait;
    use IndexHintTrait;
    use PagingTrait;

    protected $select = [];

    protected $from = [];

    /**
     * @var \SQLBuilder\Universal\Syntax\Conditions
     */
    protected $having;

    protected $groupByList = [];

    protected $groupByModifiers = [];

    protected $lockModifier;

    protected $rollupModifier;

    /**
     * SelectQuery constructor.
     */
    public function __construct()
    {
        $this->having = new Conditions();
    }

    /**
     * Accessors
     */

    /**
     * @return $this
     */
    public function all()
    {
        return $this->option('ALL');
    }

    /**
     * @return $this
     */
    public function distinct()
    {
        return $this->option('DISTINCT');
    }

    /**
     * @return $this
     */
    public function distinctRow()
    {
        return $this->option('DISTINCTROW');
    }

    /********************************************************
     * MySQL Only Options
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/select.html
     *******************************************************/

    /**
     * @return $this
     */
    public function useSqlCache()
    {
        return $this->option('SQL_CACHE');
    }

    /**
     * @return $this
     */
    public function useSqlNoCache()
    {
        return $this->option('SQL_NO_CACHE');
    }

    /**
     * @return $this
     */
    public function useSmallResult()
    {
        return $this->option('SQL_SMALL_RESULT');
    }

    /**
     * @return $this
     */
    public function useBigResult()
    {
        return $this->option('SQL_BIG_RESULT');
    }

    /**
     * @return $this
     */
    public function useBufferResult()
    {
        return $this->option('SQL_BUFFER_RESULT');
    }

    /**
     * @param $select
     *
     * @return $this
     */
    public function select($select)
    {
        if (is_array($select)) {
            $this->select = array_merge_recursive($this->select, $select);
        } else {
            $this->select = array_merge_recursive($this->select, func_get_args());
        }

        return $this;
    }

    /**
     * @param $select
     *
     * @return $this
     */
    public function setSelect($select)
    {
        if (is_array($select)) {
            $this->select = $select;
        } else {
            $this->select = func_get_args();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * ->from('posts', 'p')
     * ->from('users', 'u').
     *
     * @param      $table
     * @param null $alias
     *
     * @return $this
     */
    public function from($table, $alias = null)
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }

        return $this;
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function setFrom($table)
    {
        if (is_array($table)) {
            $this->from = $table;
        } else {
            $this->from = func_get_args();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param null|string $expr
     * @param array       $args
     *
     * @return \SQLBuilder\Universal\Syntax\Conditions
     */
    public function having($expr = null, array $args = [])
    {
        if (is_string($expr)) {
            $this->having->raw($expr, $args);
        }

        return $this->having;
    }

    /**
     * Functions support GROUP BY.
     *
     *  > SELECT FROM_DAYS(SUM(TO_DAYS(date_col))) FROM tbl_name;
     *
     * @see http://dev.mysql.com/doc/refman/5.7/en/group-by-functions.html
     * @see http://dev.mysql.com/doc/refman/5.7/en/group-by-functions-and-modifiers.html
     *
     * @param            $expr
     * @param array|null $modifiers
     *
     * @return $this
     */
    public function groupBy($expr, array $modifiers = null)
    {
        if (is_array($expr)) {
            $this->groupByList = array_merge($this->groupByList, $expr);
        } else {
            $this->groupByList[] = $expr;
        }
        if ($modifiers) {
            $this->groupByModifiers = $modifiers;
        }

        return $this;
    }

    public function removeGroupBy()
    {
        $this->groupByList = [];
    }

    /**
     * Note: SELECT FOR UPDATE does not work when used in select statement with a subquery.
     *
     * @return $this
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

    /**
     * Builders
     */

    /**
     *
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildSelectClause(BaseDriver $driver, ArgumentArray $args)
    {
        $sql   = ' ';
        $first = true;
        foreach ($this->select as $k => $v) {
            if ($first) {
                $first = false;
            } else {
                $sql .= ', ';
            }

            /* "column AS alias" OR just "column" */
            if (is_int($k)) {
                if ($v instanceof ToSqlInterface) { //TODO: $v instanceof SelectExpr ??
                    $sql .= $v->toSql($driver, $args);
                } elseif (is_array($v)) {
                    $sql .= implode(' ', $v);
                } else {
                    $sql .= $v;
                }
            } else {
                $sql .= $k . ' AS ' . $v;
            }
        }

        return $sql;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    protected function buildFromClauseMySQL(BaseDriver $driver, ArgumentArray $args)
    {
        $tableRefs = [];
        foreach ($this->from as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $v;
                if ($this->definedIndexHint($v)) {
                    $sql .= $this->buildIndexHintClauseByTableRef($v, $driver, $args);
                } elseif ($this->definedIndexHint($k)) {
                    $sql .= $this->buildIndexHintClauseByTableRef($k, $driver, $args);
                }
                $tableRefs[] = $sql;
            } elseif (is_int($k) || is_numeric($k)) {
                $sql = $driver->quoteTable($v);
                if ($this->definedIndexHint($v)) {
                    $sql .= $this->buildIndexHintClauseByTableRef($v, $driver, $args);
                }
                $tableRefs[] = $sql;
            }
        }
        if (!empty($tableRefs)) {
            return ' FROM ' . implode(', ', $tableRefs);
        }

        return '';
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    protected function buildFromClause(BaseDriver $driver, ArgumentArray $args)
    {
        $tableRefs = [];
        foreach ($this->from as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $tableRefs[] = $driver->quoteTable($k) . ' AS ' . $v;
            } elseif (is_int($k) || is_numeric($k)) {
                $tableRefs[] = $driver->quoteTable($v);
            }
        }
        if (!empty($tableRefs)) {
            return ' FROM ' . implode(', ', $tableRefs);
        }

        return '';
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \Exception
     */
    public function buildGroupByClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->groupByList)) {
            return '';
        }

        // TODO: group by modifiers, currently only support for syntax like "GROUP BY a WITH ROLLUP".
        // @see http://dev.mysql.com/doc/refman/5.7/en/group-by-modifiers.html
        $sql = ' GROUP BY ' . implode(', ', $this->groupByList);
        if ($this->groupByModifiers) {
            $sql .= ' ' . implode(' ', $this->groupByModifiers);
        }

        if ($this->rollupModifier) {
            if (!$driver instanceof MySQLDriver) {
                throw new \InvalidArgumentException('Incompatible Query Usage: rollup is only supported in MySQL.');
            }
            $sql .= ' ' . $this->rollupModifier;
        }

        return $sql;
    }

    public function buildLockModifierClauseMySQL()
    {
        if ($this->lockModifier) {
            return ' ' . $this->lockModifier;
        }

        return '';
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildHavingClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (!empty($this->having->exprs)) {
            return ' HAVING ' . $this->having->toSql($driver, $args);
        }

        return '';
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \Exception
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($driver instanceof MySQLDriver) {
            return 'SELECT'
                   . $this->buildOptionClause()
                   . $this->buildSelectClause($driver, $args)
                   . $this->buildFromClauseMySQL($driver, $args)
                   . $this->buildPartitionClause($driver, $args)
                   . $this->buildJoinClause($driver, $args)
                   . $this->buildWhereClause($driver, $args)
                   . $this->buildGroupByClause($driver, $args)
                   . $this->buildHavingClause($driver, $args)
                   . $this->buildOrderByClause($driver, $args)
                   . $this->buildPagingClause($driver, $args)
                   . $this->buildLockModifierClauseMySQL();
        }

        return 'SELECT'
               . $this->buildOptionClause()
               . $this->buildSelectClause($driver, $args)
               . $this->buildFromClause($driver, $args)
               . $this->buildJoinClause($driver, $args)
               . $this->buildWhereClause($driver, $args)
               . $this->buildGroupByClause($driver, $args)
               . $this->buildHavingClause($driver, $args)
               . $this->buildOrderByClause($driver, $args)
               . $this->buildPagingClause($driver, $args);
    }

    public function __clone()
    {
        $this->having = $this->having;
        $this->where  = $this->where;
    }
}
