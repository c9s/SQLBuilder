<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableDropForeignKey implements ToSqlInterface
{
    protected $fkSymbol;

    public function __construct($fkSymbol)
    {
        $this->fkSymbol = $fkSymbol;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'DROP FOREIGN KEY '.$driver->quoteIdentifier($this->fkSymbol);
    }
}
