<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Exception\UnsupportedDriverException;

class AlterTableDropColumn implements ToSqlInterface
{
    protected $column;

    public function __construct(Column $column) {
        $this->column = $column;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'DROP COLUMN ';
        $sql .= $driver->quoteIdentifier($this->column->name);
        return $sql;
    }
}




