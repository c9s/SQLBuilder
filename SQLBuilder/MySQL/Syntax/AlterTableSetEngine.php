<?php

namespace SQLBuilder\MySQL\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableSetEngine implements ToSqlInterface
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'ENGINE = '.$driver->deflate($this->value);
    }
}
