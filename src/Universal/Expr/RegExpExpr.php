<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

/**
 * @see http://dev.mysql.com/doc/refman/5.6/en/regexp.html
 */
class RegExpExpr implements ToSqlInterface
{
    public $exprStr;

    public $pat;

    public function __construct($exprStr, $pat)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr.' REGEXP '.$driver->deflate($this->pat, $args);
    }

    public static function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['pat']);
    }
}
