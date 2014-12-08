<?php
namespace SQLBuilder\Expr;
use SQLBuilder\Expr\Expr;
use SQLBuilder\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class InExpr extends Expr implements ToSqlInterface { 

    public $exprStr;

    public $listExpr;

    public function __construct($exprStr, array $set)
    {
        $this->exprStr = $exprStr;
        $this->listExpr = new ListExpr($set);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' IN ' . $this->listExpr->toSql($driver, $args);
    }
}
