<?php
namespace SQLBuilder\Expr;

use SQLBuilder\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

class ListExpr implements ToSqlInterface
{
    public $params;

    public function __construct(array $params) {
        $this->params = $params;
    }

    public function append($val) {
        $this->params[] = $val;
    }

    public function renderSet(BaseDriver $driver, ArgumentArray $args, array $set) 
    {
        return array_map(function($val) use($driver, $args) {
            return $driver->deflate($val, $args);
        }, $set);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        foreach($this->params as $val) {
            $sql .= ',' . $driver->deflate($val, $args);
        }
        return '(' . ltrim($sql, ',') . ')';
    }
}

