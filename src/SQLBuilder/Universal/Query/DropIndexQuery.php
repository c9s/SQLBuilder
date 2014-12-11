<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;

/*
MySQL Drop Index Syntax

    DROP INDEX index_name ON tbl_name
    DROP INDEX `PRIMARY` ON t;
*/
class DropIndexQuery implements ToSqlInterface
{

    protected $indexName;

    protected $tableName;

    public function drop($indexName) {
        $this->indexName = $indexName;
        return $this;
    }

    public function name($indexName) {
        $this->indexName = $indexName;
        return $this;
    }

    public function on($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        return 'DROP INDEX ' . $driver->quoteIdentifier($this->indexName) . ' ON ' . $driver->quoteIdentifier($this->tableName);
    }
}
