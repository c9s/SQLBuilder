<?php
namespace SQLBuilder;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Expression\BetweenExpr;
use SQLBuilder\Expression\StringExpr;
use SQLBuilder\Expression\UnaryExpr;
use SQLBuilder\Expression\BinaryExpr;
use SQLBuilder\Expression\InExpr;
use SQLBuilder\Expression\NotInExpr;

class Op { }

class AndOp extends Op {
    public function toSql(BaseDriver $driver) {
        return 'AND';
    }
}

class OrOp extends Op { 
    public function toSql(BaseDriver $driver) {
        return 'OR';
    }
}

class XorOp extends Op { 
    public function toSql(BaseDriver $driver) {
        return 'XOR';
    }
}

class NotOp extends Op { 
    public function toSql(BaseDriver $driver) {
        return '!';
    }
}


class ExpressionBuilder
{
    public $exprs = array();

    public function __construct()
    {
    }


    /**
     * http://dev.mysql.com/doc/refman/5.0/en/expressions.html
     */
    public function appendExprObject(Expr $expr) {
        if (count($this->exprs) > 0 && ! end($this->exprs) instanceof Op) {
            $this->exprs[] = new AndOp;
        }
        $this->exprs[] = $expr;
    }

    public function appendExpr($a1, $op, $a2) {
        return $this->appendExprObject(new BinaryExpr($a1, $op, $a2));
    }

    public function equal($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '=', $a2));
        return $this;
    }

    public function notEqual($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '<>', $a2));
        return $this;
    }

    public function greaterThan($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '>', $a2));
        return $this;
    }

    public function greaterOrEqual($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '>=', $a2));
        return $this;
    }

    public function lessThan($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '<', $a2));
        return $this;
    }

    public function lessOrEqual($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '<=', $a2));
        return $this;
    }

    public function __call($method,$args)
    {
        switch( $method )
        {
        case 'and':
            $this->exprs[] = new AndOp;
            return $this;
        case 'or':
            $this->exprs[] = new OrOp;
            return $this;
        case 'xor':
            $this->exprs[] = new XorOp;
            return $this;
        }
        // return call_user_func_array( array($this->parent,$method) , $args );
    }


    public function between($exprStr, $min, $max)
    {
        $this->appendExprObject(new BetweenExpr($exprStr, $min, $max));
        return $this;
    }


    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_in
     */
    public function in($exprStr, array $set)
    {
        $this->appendExprObject(new InExpr($exprStr, $set));
    }

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_not-in
     */
    public function notIn($exprStr, array $set)
    {
        $this->appendExprObject(new NotInExpr($exprStr, $set));
    }

    public function toSql(BaseDriver $driver) {
        $sql = '';
        foreach ($this->exprs as $expr) {
            $sql .= ' ' . $expr->toSql($driver);
        }
        return $sql;
    }
}



