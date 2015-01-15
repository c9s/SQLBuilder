<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use Exception;

class SelectAs implements ToSqlInterface
{
    protected $expr;

    protected $as;

    public function __construct($expr, $as)
    {
        $this->expr = $expr;
        $this->as = $as;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = '';

        if (is_string($this->expr)) {
            $sql .= $this->expr;
        } elseif ($this->expr instanceof ToSqlInterface) {
            $sql .= $this->expr->toSql($driver, $args);
        } else {
            throw new Exception('Unknown type expr');
        }
        $sql .= ' AS ' . $driver->quoteIdentifier($this->as);
        return $sql;
    }
}








