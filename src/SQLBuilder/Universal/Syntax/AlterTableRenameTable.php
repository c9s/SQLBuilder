<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Exception\UnsupportedDriverException;

class AlterTableRenameTable implements ToSqlInterface
{
    protected $fromTable;

    protected $toTable;

    public function __construct($fromTable, $toTable) {
        $this->fromTable = $fromTable;
        $this->toTable = $toTable;
    }

    public function changeTo(Column $toTable) {
        $this->toTable = $toTable;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'RENAME ';

        if ($driver instanceof SQLiteDriver) {
            throw new UnsupportedDriverException('sqlite driver is not supported.');
        }

        if (is_string($this->fromTable)) {
            $sql .= $driver->quoteIdentifier($this->fromTable);
        } elseif ($this->fromTable instanceof Column) {
            $sql .= $driver->quoteIdentifier($this->fromTable->getName());
        }

        // the 'toTable' must be a type of Column, we need at least column type to rename.
        $sql .= ' TO ' . $driver->quoteIdentifier($this->toTable->getName()) . ' ' . $this->toTable->getType();
        return $sql;
    }
}




