<?php
use SQLBuilder\Expression\ConditionsExpr;
use SQLBuilder\Criteria;

class ConditionsExprTest extends PHPUnit_Framework_TestCase
{
    public function testAppendExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        ok($driver);

        $exprBuilder = new ConditionsExpr;
        $exprBuilder->appendExpr('a', '=', 123);
        $sql = $exprBuilder->toSql($driver);
        is("a = 123",$sql);
    }

    public function testInExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->in('b', [ 'a', 'b', 'c' ]);
        $sql = $expr->toSql($driver);
        is("b IN ('a','b','c')", $sql);
    }

    public function testNotInExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->notIn('z', [ 'a', 'b', 'c' ]);
        $sql = $expr->toSql($driver);
        is("z NOT IN ('a','b','c')", $sql);
    }

    public function testEqual() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->equal('a', 1);
        $sql = $expr->toSql($driver);
        is("a = 1", $sql);
    }

    public function testNotEqual() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->notEqual('a', 1);
        $sql = $expr->toSql($driver);
        is("a <> 1", $sql);
    }


    public function likeExprProvider() {
        return [
            [ NULL ,                 "John", "name LIKE '%John%'" ],
            [ Criteria::CONTAINS ,   "John", "name LIKE '%John%'" ],
            [ Criteria::STARTS_WITH, "John", "name LIKE 'John%'" ],
            [ Criteria::ENDS_WITH,   "John", "name LIKE '%John'" ],
        ];
    }

    /**
     * @dataProvider likeExprProvider
     */
    public function testLikeExpr($criteria, $pat, $expectedSql) {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->like('name', $pat, $criteria);
        $sql = $expr->toSql($driver);
        is($expectedSql, $sql);
    }

    public function testBetweenExpr()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ConditionsExpr;
        $expr->between('created_at', date('c') , date('c', time() + 3600));
        $sql = $expr->toSql($driver);
        // is("", $sql);
    }
}

