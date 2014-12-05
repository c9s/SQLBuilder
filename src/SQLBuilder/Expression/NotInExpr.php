<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;

class NotInExpr extends InExpr { 

    public function toSql(BaseDriver $driver) {
        // TODO: check instance (ParamMarker or Variable) and quote the string if need
        return $this->exprStr . ' NOT IN (' . join(',', $this->renderSet($driver, $this->set)) . ')';
    }
}

