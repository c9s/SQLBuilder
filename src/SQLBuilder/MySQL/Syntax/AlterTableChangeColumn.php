<?php
namespace SQLBuilder\MySQL\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\ArgumentArray;

class AlterTableChangeColumn implements ToSqlInterface
{
    protected $fromColumn;

    protected $toColumn;

    public function __construct($fromColumn) {
        $this->fromColumn = $fromColumn;
    }

    public function changeTo($toColumn) {
        $this->toColumn = $toColumn;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'CHANGE COLUMN ';
        if (is_string($this->fromColumn)) {
            $sql .= $driver->quoteIdentifier($this->fromColumn);
        } elseif ($this->fromColumn instanceof Column) {
            $sql .= $driver->quoteIdentifier($this->fromColumn->toSql($driver, $args));
        }
        return $sql;
    }
}




