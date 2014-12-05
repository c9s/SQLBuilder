<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use LogicException;

class LikeExpr extends Expr { 

    public $pat;

    public $criteria;

    public function __construct($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->exprStr = $exprStr;
        $this->pat = $pat;
        $this->criteria = $criteria;
    }

    public function toSql(BaseDriver $driver) {
        $pat = $this->pat;

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
        return $this->exprStr . ' LIKE ' . $driver->deflate($pat);
    }

}
