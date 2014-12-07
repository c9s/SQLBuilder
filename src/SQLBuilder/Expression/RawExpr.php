<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class RawExpr extends Expr implements ToSqlInterface
{
    public $str;

    public $args;

    public function __construct($str, array $args = array())
    {
        $this->str = $str;
        $this->args = $args;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->str;
    }
}
