<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

/**
 * Class NotInExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class NotInExpr extends InExpr
{
    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' NOT IN ' . $this->listExpr->toSql($driver, $args);
    }
}
