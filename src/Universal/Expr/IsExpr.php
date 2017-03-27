<?php

namespace SQLBuilder\Universal\Expr;

use InvalidArgumentException;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

class IsExpr implements ToSqlInterface
{
    /**
     * @var string
     */
    public $exprStr;

    /**
     * @var bool|null|\SQLBuilder\DataType\Unknown
     */
    public $boolean;

    /**
     * IsExpr constructor.
     *
     * @param string                                 $exprStr
     * @param bool|null|\SQLBuilder\DataType\Unknown $boolean
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($exprStr, $boolean)
    {
        $this->exprStr = $exprStr;

        // Validate boolean type
        if (is_bool($boolean) || $boolean === null || $boolean instanceof Unknown) {
            $this->boolean = $boolean;
        } else {
            throw new InvalidArgumentException('Invalid boolean type');
        }
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return $this->exprStr . ' IS ' . $driver->deflate($this->boolean, $args);
    }

    /**
     * @param array $array
     *
     * @return \SQLBuilder\Universal\Expr\IsExpr
     */
    public static function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['boolean']);
    }
}
