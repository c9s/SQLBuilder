<?php
namespace SQLBuilder\Expr;

use SQLBuilder\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

class ListExpr extends ParamsExpr implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return '(' . parent::toSql($driver, $args) . ')';
    }
}

