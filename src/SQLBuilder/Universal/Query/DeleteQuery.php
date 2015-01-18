<?php
namespace SQLBuilder\Universal\Query;
use Exception;
use LogicException;
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
use SQLBuilder\MySQL\Syntax\Partition;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\Universal\Syntax\Paging;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\Universal\Traits\LimitTrait;
use SQLBuilder\Exception\IncompleteSettingsException;

/**
 * Delete Statement Query
 *
 * @code
 *
 *  $query = new SQLBuilder\Universal\Query\DeleteQuery;
 *  $query->delete(array(
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

    DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name
        [PARTITION (partition_name,...)]
        [WHERE where_condition]
        [ORDER BY ...]
        [LIMIT row_count]

 */
class DeleteQuery implements ToSqlInterface
{
    use OptionTrait;
    use JoinTrait;
    use WhereTrait;
    use LimitTrait;
    use PartitionTrait;
    use OrderByTrait;

    protected $deleteTables = array();

    /**
     * ->delete('posts', 'p')
     * ->delete('users', 'u')
     */
    public function delete($table, $alias = NULL) {
        if ($alias) {
            $this->deleteTables[$table] = $alias;
        } else {
            $this->deleteTables[] = $table;
        }
        return $this;
    }

    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildFromClause(BaseDriver $driver, ArgumentArray $args) {
        if (empty($this->deleteTables)) {
            throw new IncompleteSettingsException('DeleteQuery requires tables to delete.');
        }

        $tableRefs = array();
        foreach($this->deleteTables as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $v;
                $tableRefs[] = $sql;
            } elseif ( is_integer($k) || is_numeric($k) ) {
                $sql = $driver->quoteTable($v);
                $tableRefs[] = $sql;
            }
        }
        return ' FROM ' . join(', ', $tableRefs);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'DELETE'
            . $this->buildOptionClause()
            . $this->buildFromClause($driver, $args)
            ;

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $sql .= $this->buildJoinClause($driver, $args)
            . $this->buildWhereClause($driver, $args)
            ;

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildOrderByClause($driver, $args);
            $sql .= $this->buildLimitClause($driver, $args);
        }
        return $sql;
    }

    public function __clone() {
        $this->where = $this->where;
    }
}

