<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class UnaryExpr extends Expr implements ToSqlInterface
{
    public $op;

    public $operand;

    public function __construct($op, $operand) {
        $this->op = $op;
        $this->operand = $operand;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->op . ' ' . $this->operand;
    }
}
