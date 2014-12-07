<?php
namespace SQLBuilder\Expr;
use SQLBuilder\Expr\Expr;
use SQLBuilder\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ToSqlInterface;
use LogicException;
use SQLBuilder\ArgumentArray;

class IsNotExpr extends IsExpr implements ToSqlInterface { 
    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' IS NOT ' . $driver->deflate($this->boolean, $args);
    }
}
