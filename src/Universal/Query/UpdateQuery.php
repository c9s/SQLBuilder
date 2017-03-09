<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\Universal\Traits\OptionTrait;
use SQLBuilder\Universal\Traits\LimitTrait;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\MySQL\Traits\IndexHintTrait;
use SQLBuilder\Exception\IncompleteSettingsException;

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
 
 * @see http://dev.mysql.com/doc/refman/5.7/en/update.html for reference
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

    protected $updateTables = array();

    protected $sets = array();

    /**
     * ->update('posts', 'p')
     * ->update('users', 'u').
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

    public function set(array $sets)
    {
        $this->sets = $this->sets + $sets;

        return $this;
    }

    /****************************************************************
     * Builders
     ***************************************************************/
    public function buildSetClause(BaseDriver $driver, ArgumentArray $args)
    {
        $setClauses = array();
        foreach ($this->sets as $col => $val) {
            $setClauses[] = $col.' = '.$driver->deflate($val);
        }

        return ' SET '.implode(', ', $setClauses);
    }

    public function buildFromClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->updateTables)) {
            throw new IncompleteSettingsException('UpdateQuery requires at least one table to update.');
        }
        $tableRefs = array();
        foreach ($this->updateTables as $k => $alias) {
            /* "column AS alias" OR just "column" */
            if (is_string($k)) {
                $sql = $driver->quoteTable($k).' AS '.$alias;
                if ($driver instanceof MySQLDriver) {
                    if ($this->definedIndexHint($alias)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($alias, $driver, $args);
                    } elseif ($this->definedIndexHint($k)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($k, $driver, $args);
                    }
                }
                $tableRefs[] = $sql;
            } elseif (is_integer($k) || is_numeric($k)) {
                $sql = $driver->quoteTable($alias);
                if ($driver instanceof MySQLDriver) {
                    if ($this->definedIndexHint($alias)) {
                        $sql .= $this->buildIndexHintClauseByTableRef($alias, $driver, $args);
                    }
                }
                $tableRefs[] = $sql;
            }
        }

        return ' '.implode(', ', $tableRefs);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'UPDATE'
            .$this->buildOptionClause()
            .$this->buildFromClause($driver, $args);

        $sql .= $this->buildJoinClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $sql .= $this->buildSetClause($driver, $args)
            .$this->buildWhereClause($driver, $args)
            .$this->buildOrderByClause($driver, $args)
            ;
        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildLimitClause($driver, $args);
        }

        return $sql;
    }
}
