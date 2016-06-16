<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\ArgumentArray;

class AlterTableDrop implements ToSqlInterface
{
    protected $subquery;

    public function __construct($anything)
    {
        $this->subquery = $anything;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->subquery instanceof ToSqlInterface) {
            return 'DROP ' . $this->subquery->toSql($driver, $args);
        }
        return 'DROP ' . $this->subquery;
    }
}




