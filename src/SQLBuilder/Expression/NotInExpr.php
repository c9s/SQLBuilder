<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

class NotInExpr extends InExpr implements ToSqlInterface {

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' NOT IN ' . $this->listExpr->toSql($driver);
    }
}

