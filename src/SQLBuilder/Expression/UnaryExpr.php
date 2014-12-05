<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;

class UnaryExpr extends Expr 
{
    public $op;

    public $operand;

    public function __construct($op, $operand) {
        $this->op = $op;
        $this->operand = $operand;
    }

    public function toSql(BaseDriver $driver) {
        return $this->op . ' ' . $this->operand;
    }
}
