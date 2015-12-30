<?php
namespace SQLBuilder\Universal\Expr;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Universal\Expr\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ParamMarker;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use LogicException;


/**
 * MySQL Function Name Parsing and Resolution
 *
 * @see http://dev.mysql.com/doc/refman/5.0/en/function-resolution.html
 */
class FuncCallExpr implements ToSqlInterface
{
    public $funcName;

    /**
     * @var ListExpr
     */
    public $funcParams;

    public function __construct($funcName, array $args = array())
    {
        $this->funcName = $funcName;
        $this->funcParams = new ListExpr($args);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return $this->funcName . $this->funcParams->toSql($driver, $args);
    }

    static public function __set_state($array)
    {
        $expr =  new self($array['funcName']);
        $expr->funcParams = $array['funcParams'];
        return $expr;
    }

}



