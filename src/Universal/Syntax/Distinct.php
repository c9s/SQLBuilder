<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

class Distinct implements ToSqlInterface
{
    /**
     * @var ToSqlInterface|string
     */
    protected $expr;

    public function __construct($expr)
    {
        $this->expr = $expr;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \Exception
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->expr instanceof ToSqlInterface) {
            return 'DISTINCT ' . $this->expr->toSql($driver, $args);
        } elseif (is_string($this->expr)) {
            return 'DISTINCT ' . $this->expr;
        } else {
            throw new \InvalidArgumentException('Unsupported expression type');
        }
    }
}
