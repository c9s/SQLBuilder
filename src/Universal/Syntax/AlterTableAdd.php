<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableAdd implements ToSqlInterface
{
    protected $subquery;

    public function __construct($anything)
    {
        $this->subquery = $anything;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->subquery instanceof ToSqlInterface) {
            return 'ADD '.$this->subquery->toSql($driver, $args);
        }

        return 'ADD '.$this->subquery;
    }
}
