<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use LogicException;

class InExpr extends Expr { 

    public $set = array();

    public function __construct($exprStr, array $set)
    {
        $this->exprStr = $exprStr;
        $this->set = $set;
    }

    public function renderSet(BaseDriver $driver, array $set) {
        $values = array();
        foreach($set as $val) {
            $values[] = $driver->deflate($val);
        }
        return $values;
    }

    public function toSql(BaseDriver $driver) {
        // TODO: check instance (ParamMarker or Variable) and quote the string if need
        return $this->exprStr . ' IN (' . join(',', $this->renderSet($driver, $this->set)) . ')';
    }
}
