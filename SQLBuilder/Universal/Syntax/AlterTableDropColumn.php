<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableDropColumn implements ToSqlInterface
{
    protected $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'DROP COLUMN ';
        $sql .= $driver->quoteIdentifier($this->column->name);

        return $sql;
    }
}
