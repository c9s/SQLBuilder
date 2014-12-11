<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use LogicException;

class LikeExpr extends Expr implements ToSqlInterface { 

    public $pat;

    public $criteria;

    public function __construct($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
        $this->criteria = $criteria;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        // XXX: $pat can be a Bind object
        $isBind = $this->pat instanceof Bind;

        $pat = $isBind ? $this->pat->value : $this->pat;

        switch ($this->criteria) {
        case Criteria::CONTAINS:
            $pat = '%' . $this->pat . '%';
            break;
        case Criteria::STARTS_WITH:
            $pat = $this->pat . '%';
            break;

        case Criteria::ENDS_WITH:
            $pat = '%' . $this->pat;
            break;

        case Criteria::EXACT:
            $pat = $this->pat;
            break;

        default:
            $pat = '%' . $this->pat . '%';
            break;
        }

        if ($isBind) {
            $this->pat->setValue($pat);
        } else {
            $this->pat = $pat;
        }
        return $this->exprStr . ' LIKE ' . $driver->deflate($this->pat, $args);
    }

}
