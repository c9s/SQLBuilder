<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableDropIndex implements ToSqlInterface
{
    protected $index;

    public function __construct($indexName)
    {
        $this->indexName = $indexName;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'DROP INDEX '.$driver->quoteIdentifier($this->indexName);
    }
}
