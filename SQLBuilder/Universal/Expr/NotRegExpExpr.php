<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

class NotRegExpExpr extends RegExpExpr implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $column = $this->exprStr;
        if ($driver->quoteColumn) {
            $column = $driver->quoteIdentifier($column);
        }
        return $column . ' NOT REGEXP ' . $driver->deflate($this->pat, $args);
    }
}
