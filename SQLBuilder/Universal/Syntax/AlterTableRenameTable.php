<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableRenameTable implements ToSqlInterface
{
    protected $toTable;

    public function __construct($toTable)
    {
        $this->toTable = $toTable;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'RENAME TO '.$driver->quoteIdentifier($this->toTable);
    }
}
