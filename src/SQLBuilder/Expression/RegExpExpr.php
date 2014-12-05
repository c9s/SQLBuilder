<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use LogicException;

class RegExpExpr extends Expr { 

    public $pat;

    public function __construct($exprStr, $pat)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
    }

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' REGEXP ' . $driver->deflate($this->pat);
    }
}
