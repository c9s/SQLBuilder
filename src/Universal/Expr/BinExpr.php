<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class BinExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class BinExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $op;

    /**
     * @var string
     */
    public $operand;

    /**
     * @var string
     */
    public $operand2;

    /**
     * BinExpr constructor.
     *
     * @param string $operand
     * @param string $op
     * @param string $operand2
     */
    public function __construct($operand, $op, $operand2)
    {
        $this->op       = $op;
        $this->operand  = $operand;
        $this->operand2 = $operand2;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->operand . ' ' . $this->op . ' ' . $driver->deflate($this->operand2, $args);
    }

    /**
     * @param $array
     *
     * @return \SQLBuilder\Universal\Expr\BinExpr
     */
    public static function __set_state($array)
    {
        return new self($array['operand'], $array['op'], $array['operand2']);
    }
}
