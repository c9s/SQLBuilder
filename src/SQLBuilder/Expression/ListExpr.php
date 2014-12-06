<?php
namespace SQLBuilder\Expression;

use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\ToSqlInterface;
use LogicException;

class ListExpr extends ParamsExpr implements ToSqlInterface
{
    public function toSql(BaseDriver $driver)
    {
        return '(' . parent::toSql($driver) . ')';
    }
}

