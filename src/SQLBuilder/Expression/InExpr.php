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

    public function deflateValue(BaseDriver $driver, $val) {
        if (is_int($val) || is_float($val)) {
            return var_export($val, true);
        } elseif (is_string($val)) {
            return $driver->quote($val);
        } elseif ($val instanceof ParamMarker) {
            return $val->name;
        } else {
            throw new LogicException("Unsupported type");
        }
    }

    public function renderSet(BaseDriver $driver, array $set) {
        $values = array();
        foreach($set as $val) {
            $values[] = $this->deflateValue($driver, $val);
        }
        return $values;
    }

    public function toSql(BaseDriver $driver) {
        // TODO: check instance (ParamMarker or Variable) and quote the string if need
        return $this->exprStr . ' IN (' . join(',', $this->renderSet($driver, $this->set)) . ')';
    }
}
