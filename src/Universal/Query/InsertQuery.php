<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\OptionTrait;

/**
 * Class InsertQuery
 *
 * > INSERT INTO tbl_name (a,b,c) VALUES (1,2,3),(4,5,6),(7,8,9);.
 *
 *
 * @see     MySQL Insert Statement http://dev.mysql.com/doc/refman/5.7/en/insert.html
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class InsertQuery implements ToSqlInterface
{
    use OptionTrait;
    use PartitionTrait;

    /**
     * insert into table.
     *
     * @param string .
     */
    protected $intoTable;

    protected $values = [];

    /**
     * Should return result when updating or inserting?
     *
     * when this flag is set, the primary key will be returned.
     *
     * @var string
     */
    protected $returning;

    /**
     * @param array $values
     *
     * @return $this
     */
    public function insert(array $values)
    {
        $this->values[] = $values;

        return $this;
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function into($table)
    {
        $this->intoTable = $table;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     *
     * @return array
     */
    public function getColumnNames(BaseDriver $driver)
    {
        return array_keys($this->values[0]);
    }

    /**
     * @param $returningColumns
     *
     * @return $this
     */
    public function returning($returningColumns)
    {
        if (is_array($returningColumns)) {
            $this->returning = $returningColumns;
        } else {
            $this->returning = func_get_args();
        }

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'INSERT';

        if (!empty($this->options)) {
            $sql .= $this->buildOptionClause();
        }

        $sql .= ' INTO ' . $driver->quoteTable($this->intoTable);

        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildPartitionClause($driver, $args);
        }

        $valuesClauses = [];

        // build columns
        $columns = $this->getColumnNames($driver);

        foreach ($this->values as $values) {
            $deflatedValues = [];
            foreach ((array)$values as $key => $value) {
                $deflatedValues[] = $driver->deflate($value, $args);
            }
            $valuesClauses[] = '(' . implode(',', $deflatedValues) . ')';
        }

        $sql .= ' (' . implode(',', $columns) . ')'
                . ' VALUES ' . implode(', ', $valuesClauses);

        // Check if RETURNING is supported
        if ($this->returning && ($driver instanceof PgSQLDriver)) {
            $sql .= ' RETURNING ' . implode(',', $this->returning);
        }

        return $sql;
    }
}
