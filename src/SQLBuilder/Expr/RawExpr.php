<?php
namespace SQLBuilder\Expr;
use SQLBuilder\Expr\Expr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use InvalidArgumentException;

class RawExpr extends Expr implements ToSqlInterface
{
    public $str;

    public $args;

    public function __construct($str, array $args = array())
    {
        $this->str = $str;
        $this->args = $args;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        foreach($this->args as $k => $a) {
            if ($a instanceof Bind) {
                $args->add($a);
            } elseif (is_string($a)) {
                $args->add(new Bind($k, $a));
            } else {
                throw new InvalidArgumentException('Unsupported argument type');
            }
        }
        return $this->str;
    }
}
