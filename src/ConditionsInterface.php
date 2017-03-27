<?php
/**
 * Description ConditionsInterface.php
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */

namespace SQLBuilder;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Universal\Syntax\Conditions;

interface ConditionsInterface
{
    /**
     * http://dev.mysql.com/doc/refman/5.0/en/expressions.html.
     */
    public function append($expr);

    public function raw($raw, array $args = []);

    public function appendBinExpr($a1, $op, $a2);

    public function equal($a1, $a2);

    public function notEqual($a1, $a2);

    public function greaterThan($a1, $a2);

    public function greaterThanOrEqual($a1, $a2);

    public function lessThan($a1, $a2);

    public function lessThanOrEqual($a1, $a2);

    public function is($exprStr, $boolean);

    public function isNot($exprStr, $boolean);

    public function between($exprStr, $min, $max);

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_in.
     */
    public function in($exprStr, $expr);

    /**
     * http://dev.mysql.com/doc/refman/5.7/en/comparison-operators.html#function_not-in.
     */
    public function notIn($exprStr, array $set);

    public function like($exprStr, $pat, $criteria = Criteria::CONTAINS);

    public function regexp($exprStr, $pat);

    public function notRegexp($exprStr, $pat);

    public function group();

    public function toSql(BaseDriver $driver, ArgumentArray $args);

    public function hasExprs();

    public function notEmpty();

    public function count();

    public function compare(Conditions $conditions);
}