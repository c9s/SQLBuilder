<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class AlterTableDropPrimaryKey implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'DROP PRIMARY KEY';
    }
}
