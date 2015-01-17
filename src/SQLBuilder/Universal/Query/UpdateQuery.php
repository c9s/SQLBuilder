<?php
namespace SQLBuilder\Universal\Query;
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
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\LimitTrait;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\MySQL\Traits\IndexHintTrait;

use Exception;
use LogicException;

/**
 * update statement builder.
 *
 * @code
 *
 *  $query = new SQLBuilder\Universal\Query\UpdateQuery;
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

 * @see http://dev.mysql.com/doc/refman/5.7/en/update.html for reference
 */
class UpdateQuery implements ToSqlInterface
{
    use WhereTrait;
    use OptionTrait;
    use JoinTrait;
    use OrderByTrait;
    use LimitTrait;

    /** MySQL only traits **/
    use PartitionTrait;
    use IndexHintTrait;

    protected $updateTables = array();

    protected $sets = array();

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

    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildSetClause(BaseDriver $driver, ArgumentArray $args) {
        $setClauses = array();
        foreach($this->sets as $col => $val) {
            if (!$val instanceof Bind && !$val instanceof ParamMarker) {
                $setClauses[] = $driver->quoteColumn($col) . " = " . $driver->deflate(new Bind($col, $val));
            } else {
                $setClauses[] = $driver->quoteColumn($col) . " = " . $driver->deflate($val);
            }
        }
        return ' SET ' . join(', ', $setClauses);
    }

    public function buildUpdateTableClause(BaseDriver $driver) {
        $tableRefs = array();
        foreach($this->updateTables as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $v;
                if ($driver instanceof MySQLDriver && isset($this->indexHintOn[$k])) {
                    $sql .= $this->indexHintOn[$k]->toSql($driver, new ArgumentArray);
                }
                $tableRefs[] = $sql;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $sql = $driver->quoteTable($v);
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


    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'UPDATE'
            . $this->buildOptionClause()
            . $this->buildUpdateTableClause($driver);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildIndexHintClause($driver, $args);
        }

        $sql .= $this->buildJoinClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $sql .= $this->buildSetClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            . $this->buildOrderByClause($driver, $args)
            ;
        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildLimitClause($driver, $args);
        }
        return $sql;
    }
}

