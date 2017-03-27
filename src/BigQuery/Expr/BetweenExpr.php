<?php

namespace SQLBuilder\BigQuery\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;


/**
 * Class BetweenExpr
 *
 * @package SQLBuilder\BigQuery\Expr
 *
 * @author  Aleksey Ilyenko <assada.ua@gmail.com
 *
 * https://cloud.google.com/bigquery/docs/reference/legacy-sql#between>
 *
 */
class BetweenExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $exprStr;

    /**
     * @var string|int
     */
    public $min;

    /**
     * @var string|int
     */
    public $max;

    /**
     * BetweenExpr constructor.
     *
     * @param string     $exprStr
     * @param string|int $min
     * @param string|int $max
     */
    public function __construct($exprStr, $min, $max)
    {
        $this->exprStr = $exprStr;
        $this->min     = $min;
        $this->max     = $max;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \LogicException
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' BETWEEN ' . $driver->deflate($this->min, $args) . ' AND ' . $driver->deflate($this->max, $args);
    }

    /**
     * @param $array
     *
     * @return \SQLBuilder\BigQuery\Expr\BetweenExpr
     */
    public static function __set_state($array)
    {
        return new self($array['exprStr'], $array['min'], $array['max']);
    }
}