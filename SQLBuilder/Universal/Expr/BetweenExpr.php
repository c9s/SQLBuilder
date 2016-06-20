<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

/**
 * http://dev.mysql.com/doc/refman/5.0/en/comparison-operators.html#operator_between
 */
class BetweenExpr implements ToSqlInterface { 

    public $exprStr;

    public $min;

    public $max;

    public function __construct($exprStr, $min, $max) {
        $this->exprStr = $exprStr;
        $this->min = $min;
        $this->max = $max;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $column = $this->exprStr;
        if ($driver->quoteColumn) {
            $column = $driver->quoteIdentifier($column);
        }
        return $column . ' BETWEEN ' . $driver->deflate($this->min, $args) . ' AND ' . $driver->deflate($this->max, $args);
    }

    static public function __set_state($array)
    {
        return new self($array['exprStr'], $array['min'], $array['max']);
    }


}
