<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

class NotRegExpExpr extends RegExpExpr implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' NOT REGEXP ' . $driver->deflate($this->pat);
    }
}
