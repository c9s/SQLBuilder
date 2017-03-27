<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class RawExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class RawExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $str;

    /**
     * @var array
     */
    public $args;

    /**
     * RawExpr constructor.
     *
     * @param string $str
     * @param array  $args
     */
    public function __construct($str, array $args = [])
    {
        $this->str  = $str;
        $this->args = $args;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return mixed
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        foreach ($this->args as $k => $a) {
            if ($a instanceof Bind) {
                $args->push($a);
            } else {
                $args->push(new Bind($k, $a));
            }
        }

        return $this->str;
    }
}
