<?php
namespace SQLBuilder\Expr;
use SQLBuilder\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class NotInExpr extends InExpr implements ToSqlInterface {

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' NOT IN ' . $this->listExpr->toSql($driver, $args);
    }
}

