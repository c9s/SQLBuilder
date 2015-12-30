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

class AlterTableDropForeignKey implements ToSqlInterface
{
    protected $fkSymbol;

    public function __construct($fkSymbol) {
        $this->fkSymbol = $fkSymbol;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        return 'DROP FOREIGN KEY ' .  $driver->quoteIdentifier($this->fkSymbol);
    }
}




