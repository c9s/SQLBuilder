<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Expression\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ToSqlInterface;
use LogicException;

class IsNotExpr extends IsExpr implements ToSqlInterface { 
    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' IS NOT ' . $driver->deflate($this->boolean);
    }
}
