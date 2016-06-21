<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class NotInExpr extends InExpr implements ToSqlInterface {

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $column = $this->exprStr;
        if ($driver->quoteColumn) {
            $column = $driver->quoteIdentifier($column);
        }
        return $column . ' NOT IN ' . $this->listExpr->toSql($driver, $args);
    }
}

