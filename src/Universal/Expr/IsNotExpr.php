<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

/**
 * Class IsNotExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class IsNotExpr extends IsExpr
{
    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' IS NOT ' . $driver->deflate($this->boolean, $args);
    }
}
