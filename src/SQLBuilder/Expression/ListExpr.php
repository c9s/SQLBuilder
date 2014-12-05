<?php
namespace SQLBuilder\Expression;

use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use LogicException;

class ListExpr extends ParamsExpr { 

    public function toSql(BaseDriver $driver)
    {
        return '(' . join(',', $this->renderSet($driver, $this->set)) . ')';
    }

}

