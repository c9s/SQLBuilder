<?php
namespace SQLBuilder\MySQL\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Syntax\ColumnNames;
use SQLBuilder\Exception\UnsupportedDriverException;

class AlterTableOrderBy implements ToSqlInterface
{
    protected $columnNames;

    public function __construct(array $orders) {
        $this->columnNames = new ColumnNames($orders);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        return 'ORDER BY ' .  $this->columnNames->toSql($driver, $args);
    }
}







