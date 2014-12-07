<?php
namespace SQLBuilder\Expression;

use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

/**
 * This class is used for handling function parameters
 */
class ParamsExpr extends Expr implements ToSqlInterface { 

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
        return join(',', $this->renderSet($driver, $args, $this->params));
    }

}

