<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Universal\Expr\Expr;
use SQLBuilder\Universal\Expr\BetweenExpr;
use SQLBuilder\Universal\Expr\RawExpr;
use SQLBuilder\Universal\Expr\UnaryExpr;
use SQLBuilder\Universal\Expr\BinaryExpr;
use SQLBuilder\Universal\Expr\InExpr;
use SQLBuilder\Universal\Expr\NotInExpr;
use SQLBuilder\Universal\Expr\LikeExpr;
use SQLBuilder\Universal\Expr\RegExpExpr;
use SQLBuilder\Universal\Expr\NotRegExpExpr;
use SQLBuilder\Universal\Expr\IsExpr;
use SQLBuilder\Universal\Expr\IsNotExpr;
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

    public function append($expr) {
        if (!empty($this->exprs) && ! end($this->exprs) instanceof Op) {
            $this->exprs[] = new AndOp;
        }
        $this->exprs[] = $expr;
        return $this;
    }

    /**
     * http://dev.mysql.com/doc/refman/5.0/en/expressions.html
     */
    public function appendExprObject(Expr $expr) {
        // We duplicate the code of checking op object to avoid the extra function call.
        if (!empty($this->exprs) && ! end($this->exprs) instanceof Op) {
            $this->exprs[] = new AndOp;
        }
        $this->exprs[] = $expr;
        return $this;
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

    public function greaterThanOrEqual($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '>=', $a2));
        return $this;
    }

    public function lessThan($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '<', $a2));
        return $this;
    }

    public function lessThanOrEqual($a1, $a2)
    {
        $this->appendExprObject(new BinaryExpr($a1, '<=', $a2));
        return $this;
    }

    public function __call($method, $args)
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
        return $this;
    }

    public function isNot($exprStr, $boolean) {
        $this->appendExprObject(new IsNotExpr($exprStr, $boolean));
        return $this;
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
        return $this;
    }

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_not-in
     */
    public function notIn($exprStr, array $set)
    {
        $this->appendExprObject(new NotInExpr($exprStr, $set));
        return $this;
    }

    public function like($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->appendExprObject(new LikeExpr($exprStr, $pat, $criteria));
        return $this;
    }

    public function regexp($exprStr, $pat) {
        $this->appendExprObject(new RegExpExpr($exprStr, $pat));
        return $this;
    }

    public function notRegexp($exprStr, $pat) {
        $this->appendExprObject(new NotRegExpExpr($exprStr, $pat));
        return $this;
    }

    public function group() {
        $conds = new GroupConditions($this);
        $this->append($conds);
        return $conds;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = '';
        foreach ($this->exprs as $expr) {
            $sql .= ' ' . $expr->toSql($driver, $args);
        }
        return ltrim($sql);
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

