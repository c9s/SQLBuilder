<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class UnaryExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class UnaryExpr implements ToSqlInterface
{
    public $op;

    public $operand;

    /**
     * UnaryExpr constructor.
     *
     * @param $op
     * @param $operand
     */
    public function __construct($op, $operand)
    {
        $this->op      = $op;
        $this->operand = $operand;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->op . ' ' . $driver->deflate($this->operand, $args);
    }

    /**
     * @param array $array
     *
     * @return \SQLBuilder\Universal\Expr\UnaryExpr
     */
    public static function __set_state(array $array)
    {
        return new self($array['op'], $array['operand']);
    }
}
