<?php
namespace SQLBuilder\Universal\Expr;

use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;
use InvalidArgumentException;

class ListExpr implements ToSqlInterface
{
    protected $expr;

    public function __construct($expr) {
        $this->expr = $expr;
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
        if (is_array($this->expr)) {
            foreach($this->expr as $val) {
                $sql .= ',' . $driver->deflate($val, $args);
            }
            $sql = ltrim($sql, ',');
        } elseif ($this->expr instanceof ToSqlInterface ) {
            $sql = $driver->deflate($this->expr, $args);
        } else {
            throw new InvalidArgumentException('Invalid expr type');
        }
        return '(' . $sql . ')';
    }
}

