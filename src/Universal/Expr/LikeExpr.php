<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\Criteria;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class LikeExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class LikeExpr implements ToSqlInterface
{
    /**
     * @var \SQLBuilder\Bind|mixed
     */
    public $pat;

    /**
     * @var int
     */
    public $criteria;

    /**
     * @var string
     */
    public $exprStr;

    /**
     * LikeExpr constructor.
     *
     * @param string     $exprStr
     * @param Bind|mixed $pat
     * @param int        $criteria
     */
    public function __construct($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->exprStr  = $exprStr;
        $this->pat      = $pat;
        $this->criteria = $criteria;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        // XXX: $pat can be a Bind object
        $isBind = $this->pat instanceof Bind;

        $pat = $isBind ? $this->pat->getValue() : $this->pat;

        switch ($this->criteria) {
            case Criteria::CONTAINS:
                $pat = '%' . $pat . '%';
                break;
            case Criteria::STARTS_WITH:
                $pat .= '%';
                break;

            case Criteria::ENDS_WITH:
                $pat = '%' . $pat;
                break;

            case Criteria::EXACT:
                break;

            default:
                $pat = '%' . $pat . '%';
                break;
        }

        if ($isBind) {
            $this->pat->setValue($pat);
        } else {
            $this->pat = $pat;
        }

        return $this->exprStr . ' LIKE ' . $driver->deflate($this->pat, $args);
    }

    /**
     * @param array $array
     *
     * @return \SQLBuilder\Universal\Expr\LikeExpr
     */
    public static function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['pat'], $array['criteria']);
    }
}
