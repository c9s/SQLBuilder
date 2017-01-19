<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;
use SQLBuilder\Universal\Traits\IfExistsTrait;
use SQLBuilder\Universal\Traits\RestrictTrait;
use SQLBuilder\Universal\Traits\CascadeTrait;

/**
 
 */
class DropIndexQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;
    use IfExistsTrait;
    use CascadeTrait;
    use RestrictTrait;

    protected $indexName;

    protected $tableName;

    /**
     * MySQL.
     */
    protected $lockType;

    /**
     * MySQL.
     */
    protected $algorithm;

    public function drop($indexName)
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function on($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * MySQL 5.6.6.
     *
     * valid values: {DEFAULT|NONE|SHARED|EXCLUSIVE}
     */
    public function lock($lockType)
    {
        $this->lockType = $lockType;

        return $this;
    }

    /**
     * MySQL 5.6.6.
     *
     * valid values: {DEFAULT|INPLACE|COPY}
     */
    public function algorithm($algorithm)
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'DROP INDEX';

        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildConcurrentlyClause($driver, $args);
        }

        $sql .= ' '.$driver->quoteIdentifier($this->indexName);

        $sql .= $this->buildIfExistsClause($driver, $args);

        if ($driver instanceof MySQLDriver) {
            if (!$this->tableName) {
                throw new IncompleteSettingsException('tableName is required. Use on($tableName) to specify one.');
            }
            $sql .= ' ON '.$driver->quoteIdentifier($this->tableName);

            if ($this->lockType) {
                $sql .= ' LOCK = '.$this->lockType;
            }
            if ($this->algorithm) {
                $sql .= ' ALGORITHM = '.$this->algorithm;
            }
        }

        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildCascadeClause();
            $sql .= $this->buildRestrictClause();
        }

        return $sql;
    }
}
