<?php

namespace SQLBuilder\Universal\Expr;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * @see http://dev.mysql.com/doc/refman/5.6/en/regexp.html
 */
class RegExpExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $exprStr;

    public $pat;

    /**
     * RegExpExpr constructor.
     *
     * @param string $exprStr
     * @param        $pat
     */
    public function __construct($exprStr, $pat)
    {
        $this->exprStr = $exprStr;
        $this->pat     = $pat;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' REGEXP ' . $driver->deflate($this->pat, $args);
    }

    /**
     * @param array $array
     *
     * @return \SQLBuilder\Universal\Expr\RegExpExpr
     */
    public static function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['pat']);
    }
}
