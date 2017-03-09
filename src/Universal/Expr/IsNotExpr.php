<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class IsNotExpr extends IsExpr implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr.' IS NOT '.$driver->deflate($this->boolean, $args);
    }
}
