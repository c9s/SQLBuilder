<?php

namespace SQLBuilder\Universal\Expr;

use InvalidArgumentException;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;

/**
 * Class ListExpr
 *
 * @package SQLBuilder\Universal\Expr
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class ListExpr implements ToSqlInterface
{
    protected $expr;

    /**
     * ListExpr constructor.
     *
     * @param string|array|ToSqlInterface|Raw $expr
     */
    public function __construct($expr)
    {
        $this->expr = $expr;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        if (is_array($this->expr)) {
            foreach ((array)$this->expr as $val) {
                $sql .= ',' . $driver->deflate($val, $args);
            }
            $sql = ltrim($sql, ',');
        } elseif ($this->expr instanceof ToSqlInterface) {
            $sql = $driver->deflate($this->expr, $args);
        } elseif ($this->expr instanceof Raw) {
            $sql = $this->expr->__toString();
        } elseif (is_string($this->expr)) {
            $sql = $this->expr;
        } else {
            throw new InvalidArgumentException('Invalid expr type');
        }

        return '(' . $sql . ')';
    }
}
