<?php

namespace SQLBuilder\BigQuerySQL\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;


/**
 * Class BetweenExpr
 *
 * @package SQLBuilder\BigQuerySQL\Expr
 *
 * @author  Aleksey Ilyenko <assada.ua@gmail.com
 *
 * https://cloud.google.com/bigquery/docs/reference/legacy-sql#between>
 *
 */
class BetweenExpr implements ToSqlInterface
{
    public $exprStr;

    public $min;

    public $max;

    public function __construct($exprStr, $min, $max)
    {
        $this->exprStr = $exprStr;
        $this->min     = $min;
        $this->max     = $max;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' BETWEEN ' . $driver->deflate($this->min, $args) . ' AND ' . $driver->deflate($this->max, $args);
    }

    public static function __set_state($array)
    {
        return new self($array['exprStr'], $array['min'], $array['max']);
    }
}