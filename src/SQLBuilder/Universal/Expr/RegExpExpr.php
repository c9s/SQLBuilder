<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use LogicException;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

/**
 * @see http://dev.mysql.com/doc/refman/5.6/en/regexp.html
 */
class RegExpExpr implements ToSqlInterface { 

    public $pat;

    public function __construct($exprStr, $pat)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' REGEXP ' . $driver->deflate($this->pat, $args);
    }
}
