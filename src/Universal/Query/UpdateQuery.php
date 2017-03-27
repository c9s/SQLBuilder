<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\MySQL\Traits\IndexHintTrait;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\LimitTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\WhereTrait;

/**
 * Class UpdateQuery
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
 * @see     http://dev.mysql.com/doc/refman/5.7/en/update.html for reference
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class UpdateQuery implements ToSqlInterface
{
    use WhereTrait;
    use OptionTrait;
    use JoinTrait;
    use OrderByTrait;
    use LimitTrait;

    /* MySQL only traits **/
    use PartitionTrait;
    use IndexHintTrait;

    protected $updateTables = [];

    protected $sets = [];

    /**
     * ->update('posts', 'p')
     * ->update('users', 'u').
     *
     * @param      $table
     * @param null $alias
     *
     * @return $this
     */
    public function update($table, $alias = null)
    {
        if ($alias) {
            $this->updateTables[$table] = $alias;
        } else {
            $this->updateTables[] = $table;
        }

        return $this;
    }

    /**
     * @param array $sets
     *
     * @return $this
     */
    public function set(array $sets)
    {
        $this->sets = array_merge_recursive($this->sets, $sets);

        return $this;
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
    public function buildSetClause(BaseDriver $driver, ArgumentArray $args)
    {
        $setClauses = [];
        foreach ($this->sets as $col => $val) {
            $setClauses[] = $col . ' = ' . $driver->deflate($val);
        }

        return ' SET ' . implode(', ', $setClauses);
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \SQLBuilder\Exception\IncompleteSettingsException
     */
    public function buildFromClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->updateTables)) {
            throw new IncompleteSettingsException('UpdateQuery requires at least one table to update.');
        }
        $tableRefs = [];
        foreach ($this->updateTables as $k => $alias) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k) . ' AS ' . $alias;
                if ($driver instanceof MySQLDriver) {
                    if ($this->definedIndexHint($alias)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($alias, $driver, $args);
                    } elseif ($this->definedIndexHint($k)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($k, $driver, $args);
                    }
                }
                $tableRefs[] = $sql;
            } elseif (is_int($k) || is_numeric($k)) {
                $sql = $driver->quoteTable($alias);
                if ($driver instanceof MySQLDriver) {
                    if ($this->definedIndexHint($alias)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($alias, $driver, $args);
                    }
                }
                $tableRefs[] = $sql;
            }
        }

        return ' ' . implode(', ', $tableRefs);
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \SQLBuilder\Exception\IncompleteSettingsException
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'UPDATE'
               . $this->buildOptionClause()
               . $this->buildFromClause($driver, $args);

        $sql .= $this->buildJoinClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $sql .= $this->buildSetClause($driver, $args)
                . $this->buildWhereClause($driver, $args)
                . $this->buildOrderByClause($driver, $args);
        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildLimitClause($driver, $args);
        }

        return $sql;
    }
}
