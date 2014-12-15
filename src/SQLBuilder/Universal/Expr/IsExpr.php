<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Universal\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use LogicException;

class IsExpr implements ToSqlInterface { 

    public $exprStr;

    public $boolean;

    public function __construct($exprStr, $boolean)
    {
        $this->exprStr = $exprStr;

        // Validate boolean type
        if (!is_bool($boolean) && !is_null($boolean) && ! $boolean instanceof Unknown) {
            throw new LogicException('Invalid boolean type');
        }

        $this->boolean = $boolean;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->exprStr . ' IS ' . $driver->deflate($this->boolean, $args);
    }
}
