<?php

namespace SQLBuilder\BigQuery\Syntax;

use SQLBuilder\BigQuery\Expr\BetweenExpr;

/**
 * Class Conditions
 *
 * @package SQLBuilder\BigQuery\Syntax
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class Conditions extends \SQLBuilder\Universal\Syntax\Conditions
{
    public function between($exprStr, $min, $max)
    {
        $this->exprs[] = new BetweenExpr($exprStr, $min, $max);

        return $this;
    }

}