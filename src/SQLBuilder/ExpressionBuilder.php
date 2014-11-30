<?php
namespace SQLBuilder;

class Op { }

class AndOp extends Op {

    public function __toString() {
        return 'AND';
    }

}

class OrOp extends Op { 
    public function __toString() {
        return 'OR';
    }
}

class XorOp extends Op { 
    public function __toString() {
        return 'XOR';
    }
}

class NotOp extends Op { 
    public function __toString() {
        return '!';
    }
}

class Variable { 

    public $var;

    public function __construct($var)
    {
        $this->var = $var;
    }
}

class Expr { 

}

class UnaryExpr extends Expr 
{
    public $op;

    public $operand;

    public function __construct($op, $operand) {
        $this->op = $op;
        $this->operand = $operand;
    }
}

class BinaryExpr extends Expr 
{

    public $op;

    public $operand;

    public $operand2;

    public function __construct($operand, $op, $operand2) {
        $this->op = $op;
        $this->operand = $operand;
        $this->operand2 = $operand2;
    }
}


/**
 * http://dev.mysql.com/doc/refman/5.0/en/comparison-operators.html#operator_between
 */
class BetweenExpr extends Expr { 

    public $exprStr;

    public $min;

    public $max;

    public function __construct($exprStr, $min, $max) {
        $this->exprStr = $exprStr;
        $this->min = $min;
        $this->max = $max;
    }
}

class InExpr extends Expr { 

    public $set = array();

    public function __construct($exprStr, array $set)
    {
        $this->exprStr = $exprStr;
        $this->set = $set;
    }
}

class NotInExpr extends InExpr { 

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
        if (! end($this->exprs) instanceof Op) {
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


}



