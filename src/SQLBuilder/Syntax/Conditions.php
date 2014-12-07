<?php
namespace SQLBuilder\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Expr\Expr;
use SQLBuilder\Expr\BetweenExpr;
use SQLBuilder\Expr\RawExpr;
use SQLBuilder\Expr\UnaryExpr;
use SQLBuilder\Expr\BinaryExpr;
use SQLBuilder\Expr\InExpr;
use SQLBuilder\Expr\NotInExpr;
use SQLBuilder\Expr\LikeExpr;
use SQLBuilder\Expr\RegExpExpr;
use SQLBuilder\Expr\NotRegExpExpr;
use SQLBuilder\Expr\IsExpr;
use SQLBuilder\Expr\IsNotExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use Countable;
use Exception;

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


class Conditions implements ToSqlInterface, Countable
{
    protected $exprs = array();

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

    public function appendExpr($raw, array $args = array()) {
        return $this->appendExprObject(new RawExpr($raw, $args));
    }

    public function appendBinExpr($a1, $op, $a2) {
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
        throw new Exception("Invalid method call: $method");
        // return call_user_func_array(array($this->parent,$method) , $args );
    }

    public function is($exprStr, $boolean) {
        $this->appendExprObject(new IsExpr($exprStr, $boolean));
    }

    public function isNot($exprStr, $boolean) {
        $this->appendExprObject(new IsNotExpr($exprStr, $boolean));
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

    public function like($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->appendExprObject(new LikeExpr($exprStr, $pat, $criteria));
    }

    public function regExp($exprStr, $pat) {
        $this->appendExprObject(new RegExpExpr($exprStr, $pat));
    }

    public function notRegExp($exprStr, $pat) {
        $this->appendExprObject(new NotRegExpExpr($exprStr, $pat));
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $clauses = array();
        foreach ($this->exprs as $expr) {
            $clauses[] = $expr->toSql($driver, $args);
        }
        return join(' ',$clauses);
    }

    public function hasExprs() {
        return count($this->exprs) > 0;
    }

    public function notEmpty() {
        return count($this->exprs) > 0;
    }

    public function count() {
        return count($this->exprs);
    }
}

