<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use LogicException;

class NotRegExpExpr extends RegExpExpr 
{
    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' NOT REGEXP ' . $driver->deflate($this->pat);
    }
}
