<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class InExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class InExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $exprStr;

    public $listExpr;

    /**
     * InExpr constructor.
     *
     * @param string                                      $exprStr
     * @param string|array|ToSqlInterface|\SQLBuilder\Raw $expr
     */
    public function __construct($exprStr, $expr)
    {
        $this->exprStr  = $exprStr;
        $this->listExpr = new ListExpr($expr);
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' IN ' . $this->listExpr->toSql($driver, $args);
    }
}
