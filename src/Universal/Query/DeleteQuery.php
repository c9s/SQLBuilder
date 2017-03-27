<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\LimitTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\WhereTrait;

/**
 * Class DeleteQuery
 *
 * Delete Statement Query.
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
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class DeleteQuery implements ToSqlInterface
{
    use OptionTrait;
    use JoinTrait;
    use WhereTrait;
    use LimitTrait;
    use PartitionTrait;
    use OrderByTrait;

    protected $deleteTables = [];

    /**
     * @param      $table
     * @param null $alias
     *
     * @return \SQLBuilder\Universal\Query\DeleteQuery
     */
    public function from($table, $alias = null)
    {
        return $this->delete($table, $alias);
    }

    /**
     * ->delete('posts', 'p')
     * ->delete('users', 'u').
     *
     * @param      $table
     * @param null $alias
     *
     * @return $this
     */
    public function delete($table, $alias = null)
    {
        if ($alias) {
            $this->deleteTables[$table] = $alias;
        } else {
            $this->deleteTables[] = $table;
        }

        return $this;
    }

    /**
     * Builders
     *
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \SQLBuilder\Exception\IncompleteSettingsException
     */
    public function buildFromClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->deleteTables)) {
            throw new IncompleteSettingsException('DeleteQuery requires tables to delete.');
        }

        $tableRefs = [];
        foreach ($this->deleteTables as $k => $v) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql         = $driver->quoteTable($k) . ' AS ' . $v;
                $tableRefs[] = $sql;
            } elseif (is_int($k) || is_numeric($k)) {
                $sql         = $driver->quoteTable($v);
                $tableRefs[] = $sql;
            }
        }

        return ' FROM ' . implode(', ', $tableRefs);
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
        $sql = 'DELETE'
               . $this->buildOptionClause()
               . $this->buildFromClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $sql .= $this->buildJoinClause($driver, $args)
                . $this->buildWhereClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildOrderByClause($driver, $args);
            $sql .= $this->buildLimitClause($driver, $args);
        }

        return $sql;
    }

    public function __clone()
    {
        $this->where = $this->where;
    }
}
