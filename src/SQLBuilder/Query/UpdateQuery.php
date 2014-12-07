<?php
namespace SQLBuilder\Query;
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
use Exception;
use LogicException;

/**
 * update statement builder.
 *
 * @code
 *
 *  $query = new SQLBuilder\Query\UpdateQuery;
 *  $query->update(array(
 *      'name' => 'foo',
 *      'values' => 'bar',
 *  ));
 *  $sql = $query->toSql($driver, $args);
 *
 * @code
 *
 * The fluent interface rules of Query objects
 *
 *    1. setters should return self, since there is no return value.
 *    2. getters should be just what they are.
 *    3. modifier can set / append data and return self
 *
 *
 * MySQL Update Syntax:

    UPDATE [LOW_PRIORITY] [IGNORE] table_reference
        SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
        [WHERE where_condition]
        [ORDER BY ...]
        [LIMIT row_count]

 * MySQL Update Multi-table syntax:

    UPDATE [LOW_PRIORITY] [IGNORE] table_references
        SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
        [WHERE where_condition]


 */
class UpdateQuery implements ToSqlInterface
{
    protected $options = array();

    protected $updateTables = array();

    protected $sets = array();

    protected $where;

    protected $joins = array();

    protected $orderByList = array();

    protected $indexHintOn = array();

    protected $limit;


    static public $BindValues = TRUE;

    public function __construct()
    {
        $this->where = new Conditions;
    }

    /**
     * MySQL Update Options:
     *
     * [LOW_PRIORITY] [IGNORE]
     */
    public function option($options) 
    {
        if (is_array($options)) {
            $this->options = $this->options + $options;
        } else {
            $this->options = $this->options + func_get_args();
        }
        return $this;
    }

    /**
     * ->update('posts', 'p')
     * ->update('users', 'u')
     */
    public function update($table, $alias = NULL) {
        if ($alias) {
            $this->updateTables[$table] = $alias;
        } else {
            $this->updateTables[] = $table;
        }
        return $this;
    }


    public function set(array $sets) {
        if (is_array($sets)) {
            $this->sets = $this->sets + $sets;
        } else {
            $this->sets = $this->sets + func_get_args();
        }
        return $this;
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
     * LIMIT clauses
     *******************************************************/
    public function limit($limit)
    {
        $this->limit = $limit;
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

    public function buildSetClause(BaseDriver $driver, ArgumentArray $args) {
        $varCnt = 1;
        $setClauses = array();
        foreach($this->sets as $col => $val) {
            // use static $BindValues and check variable types
            if (static::$BindValues && (!$val instanceof Bind && !$val instanceof ParamMarker)) {
                // XXX: we should prefer column names than by the incremental index.
                $setClauses[] = $driver->quoteColumn($col) . " = " . $driver->deflate(new Bind("p" . $varCnt++,$val));
            } else {
                $setClauses[] = $driver->quoteColumn($col) . " = " . $driver->deflate($val);
            }
        }
        return ' SET ' . join(', ', $setClauses);
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

    public function buildUpdateTableClause(BaseDriver $driver) {
        $tableRefs = array();
        foreach($this->updateTables as $k => $v) {
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
            return ' ' . join(', ', $tableRefs);
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
        if ($this->limit) {
            return ' LIMIT ' . intval($this->limit);
        }
        return '';
    }

    public function buildWhereClause(BaseDriver $driver, ArgumentArray $args) {
        if ($this->where->hasExprs()) {
            return ' WHERE ' . $this->where->toSql($driver, $args);
        }
        return '';
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'UPDATE'
            . $this->buildOptionClause()
            . $this->buildUpdateTableClause($driver)
            . $this->buildSetClause($driver, $args)
            . $this->buildIndexHintClause($driver, $args)
            . $this->buildJoinClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildOrderByClause($driver, $args)
            . $this->buildLimitClause($driver, $args)
            ;
        return $sql;
    }
}

