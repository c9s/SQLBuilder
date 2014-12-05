<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Expression\ListExpr;
use SQLBuilder\Driver\BaseDriver;

class InExpr extends Expr { 

    public $exprStr;

    public $listExpr;

    public function __construct($exprStr, array $set)
    {
        $this->exprStr = $exprStr;
        $this->listExpr = new ListExpr($set);
    }

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' IN ' . $this->listExpr->toSql($driver);
    }
}
