<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class FuncCallExpr
 *
 * MySQL Function Name Parsing and Resolution.
 *
 * @see     http://dev.mysql.com/doc/refman/5.0/en/function-resolution.html
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class FuncCallExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $funcName;

    /**
     * @var ListExpr
     */
    public $funcParams;

    /**
     * FuncCallExpr constructor.
     *
     * @param string $funcName
     * @param array  $args
     */
    public function __construct($funcName, array $args = [])
    {
        $this->funcName   = $funcName;
        $this->funcParams = new ListExpr($args);
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->funcName . $this->funcParams->toSql($driver, $args);
    }

    /**
     * @param $array
     *
     * @return \SQLBuilder\Universal\Expr\FuncCallExpr
     */
    public static function __set_state($array)
    {
        $expr             = new self($array['funcName']);
        $expr->funcParams = $array['funcParams'];

        return $expr;
    }
}
