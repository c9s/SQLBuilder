<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Universal\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use InvalidArgumentException;

class IsExpr implements ToSqlInterface { 

    public $exprStr;

    public $boolean;

    public function __construct($exprStr, $boolean)
    {
        $this->exprStr = $exprStr;

        // Validate boolean type
        if (is_bool($boolean) || $boolean === NULL || $boolean instanceof Unknown) {
            $this->boolean = $boolean;
        } else {
            throw new InvalidArgumentException('Invalid boolean type');
        }
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $column = $this->exprStr;
        if ($driver->quoteColumn) {
            $column = $driver->quoteIdentifier($column);
        }
        return $column . ' IS ' . $driver->deflate($this->boolean, $args);
    }

    static public function __set_state(array $array)
    {
        return new self($array['exprStr'], $array['boolean']);
    }

}
