<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class BinaryExpr implements ToSqlInterface
{

    public $op;

    public $operand;

    public $operand2;

    public function __construct($operand, $op, $operand2) {
        $this->op = $op;
        $this->operand = $operand;
        $this->operand2 = $operand2;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->operand . ' ' . $this->op . ' ' . $driver->deflate($this->operand2);
    }
}
