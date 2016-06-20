<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Universal\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class InExpr implements ToSqlInterface { 

    public $exprStr;

    public $listExpr;

    public function __construct($exprStr, $expr)
    {
        $this->exprStr = $exprStr;
        $this->listExpr = new ListExpr($expr);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $column = $this->exprStr;
        if ($driver->quoteColumn) {
            $column = $driver->quoteIdentifier($column);
        }
        return $column . ' IN ' . $this->listExpr->toSql($driver, $args);
    }
}
