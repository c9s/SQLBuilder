<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;

/**
 * http://dev.mysql.com/doc/refman/5.0/en/comparison-operators.html#operator_between
 */
class BetweenExpr extends Expr { 

    public $exprStr;

    public $min;

    public $max;

    public function __construct($exprStr, $min, $max) {
        $this->exprStr = $exprStr;
        $this->min = $min;
        $this->max = $max;
    }

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' BETWEEN ' . $this->min . ' AND ' . $this->max;
    }
}
