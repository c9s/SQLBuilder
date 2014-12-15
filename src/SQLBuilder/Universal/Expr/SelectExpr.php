<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use LogicException;

class SelectExpr implements ToSqlInterface { 

    public $expr;

    public $as;

    public function __construct($expr, $as = NULL)
    {
        $this->expr = $expr; // XXX: could be a Function call expr
        $this->as = $as;
    }

    public function deflateExpr($expr, BaseDriver $driver, ArgumentArray $args) {
        return $expr instanceof ToSqlInterface ? $expr->toSql($driver $args) : $expr;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        if ($this->as) {
            return $this->expr . ' AS ' . $this->as;
        } else {
            return $this->expr . ' AS ' . $this->as;
        }
    }

}
