<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;

class NotInExpr extends InExpr {

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' NOT IN ' . $this->listExpr->toSql($driver);
    }
}

