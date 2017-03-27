<?php

namespace SQLBuilder\Universal\Syntax;

use BadMethodCallException;
use Countable;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ConditionsInterface;
use SQLBuilder\Criteria;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Expr\BetweenExpr;
use SQLBuilder\Universal\Expr\BinExpr;
use SQLBuilder\Universal\Expr\InExpr;
use SQLBuilder\Universal\Expr\LikeExpr;
use SQLBuilder\Universal\Expr\NotInExpr;
use SQLBuilder\Universal\Expr\NotRegExpExpr;
use SQLBuilder\Universal\Expr\RawExpr;
use SQLBuilder\Universal\Expr\RegExpExpr;
use SQLBuilder\Universal\Expr\UnaryExpr;

require __DIR__ . '/../Expr/UnaryExpr.php';
require __DIR__ . '/../Expr/BinExpr.php';

class Op
{
}

class AndOp extends Op
{
    public function __toString()
    {
        return 'AND';
    }
}

class OrOp extends Op
{
    public function __toString()
    {
        return 'OR';
    }
}

class XorOp extends Op
{
    public function __toString()
    {
        return 'XOR';
    }
}

class Conditions implements ToSqlInterface, Countable, ConditionsInterface
{
    public $exprs;

    public function __construct(array $exprs = [])
    {
        $this->exprs = $exprs;
    }

    /**
     * http://dev.mysql.com/doc/refman/5.0/en/expressions.html.
     */
    public function append($expr)
    {
        $this->exprs[] = $expr;

        return $this;
    }

    public function raw($raw, array $args = [])
    {
        $this->exprs[] = new RawExpr($raw, $args);

        return $this;
    }

    public function appendBinExpr($a1, $op, $a2)
    {
        $this->exprs[] = new BinExpr($a1, $op, $a2);

        return $this;
    }

    public function equal($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '=', $a2);

        return $this;
    }

    public function notEqual($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '<>', $a2);

        return $this;
    }

    public function greaterThan($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '>', $a2);

        return $this;
    }

    public function greaterThanOrEqual($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '>=', $a2);

        return $this;
    }

    public function lessThan($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '<', $a2);

        return $this;
    }

    public function lessThanOrEqual($a1, $a2)
    {
        $this->exprs[] = new BinExpr($a1, '<=', $a2);

        return $this;
    }

    public function __call($method, $args)
    {
        switch ($method) {
            case 'and':
                $this->exprs[] = new AndOp();

                return $this;
            case 'or':
                $this->exprs[] = new OrOp();

                return $this;
            case 'xor':
                $this->exprs[] = new XorOp();

                return $this;
        }
        throw new BadMethodCallException("Invalid method call: $method");
    }

    public function is($exprStr, $boolean)
    {
        $this->exprs[] = new BinExpr($exprStr, 'IS', $boolean);

        return $this;
    }

    public function isNot($exprStr, $boolean)
    {
        $this->exprs[] = new BinExpr($exprStr, 'IS NOT', $boolean);

        return $this;
    }

    public function between($exprStr, $min, $max)
    {
        $this->exprs[] = new BetweenExpr($exprStr, $min, $max);

        return $this;
    }

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_in.
     */
    public function in($exprStr, $expr)
    {
        $this->exprs[] = new InExpr($exprStr, $expr);

        return $this;
    }

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_not-in.
     */
    public function notIn($exprStr, array $set)
    {
        $this->exprs[] = new NotInExpr($exprStr, $set);

        return $this;
    }

    public function like($exprStr, $pat, $criteria = Criteria::CONTAINS)
    {
        $this->exprs[] = new LikeExpr($exprStr, $pat, $criteria);

        return $this;
    }

    public function regexp($exprStr, $pat)
    {
        $this->exprs[] = new RegExpExpr($exprStr, $pat);

        return $this;
    }

    public function notRegexp($exprStr, $pat)
    {
        $this->exprs[] = new NotRegExpExpr($exprStr, $pat);

        return $this;
    }

    public function group()
    {
        $group         = new GroupConditions($this);
        $this->exprs[] = $group;

        return $group;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';

        // By default we treat all expressions are concatenated with "AND" op.
        // If there is a specific op, then the specific op will be used.
        $len = count($this->exprs);
        for ($i = 0; $i < $len; ++$i) {
            $expr = $this->exprs[$i];
            if ($expr instanceof Op) {
                if ($expr instanceof OrOp) {
                    $sql .= ' OR ';
                } elseif ($expr instanceof AndOp) {
                    $sql .= ' AND ';
                } else {
                    $sql .= ' ' . $expr->__toString() . ' ';
                }
                $expr = $this->exprs[++$i];
            } else {
                if ($i > 0) {
                    $sql .= ' AND ';
                }
            }

            if ($expr instanceof ToSqlInterface) {
                $sql .= $expr->toSql($driver, $args);
            } elseif (is_string($expr)) {
                $sql .= $expr;
            } else {
                $sql .= $driver->deflate($expr);
            }
        }

        return $sql;
    }

    public function hasExprs()
    {
        return !empty($this->exprs);
    }

    public function notEmpty()
    {
        return !empty($this->exprs);
    }

    public function count()
    {
        return count($this->exprs);
    }

    public function compare(Conditions $conditions)
    {
        $countExpr = count($this->exprs);

        if ($countExpr !== count($conditions->exprs)) {
            return false;
        }

        for ($i = 0; $i < $countExpr; ++$i) {
            $a = $this->exprs[$i];
            $b = $conditions->exprs[$i];

            if (!$a instanceof $b) {
                return false;
            }

            if ($a instanceof BinExpr) {
                if ($a->op !== $b->op) {
                    return false;
                }
                if ($a->operand !== $b->operand) {
                    return false;
                }

                if ($a->operand2 instanceof Bind) {
                    if (!$b->operand2 instanceof Bind) {
                        return false;
                    }
                    if ($a->operand2->compare($b)) {
                        return false;
                    }
                } else {
                    if ($a->operand2 !== $b->operand2) {
                        return false;
                    }
                }
            } elseif ($a instanceof UnaryExpr) {
                if ($a->op !== $b->op) {
                    return false;
                }
                if ($a->operand instanceof Bind) {
                    if (!$b->operand instanceof Bind) {
                        return false;
                    }
                    if ($a->operand->compare($b)) {
                        return false;
                    }
                } else {
                    if ($a->operand !== $b->operand) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function __set_state($array)
    {
        if (isset($array['exprs'])) {
            return new self($array['exprs']);
        }

        return new self();
    }
}
