<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;

class StringExpr extends Expr 
{
    public $str;

    public function __construct($str)
    {
        $this->str = $str;
    }

    public function toSql(BaseDriver $driver)
    {
        return $this->str;
    }
}
