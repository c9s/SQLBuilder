<?php
use SQLBuilder\ExpressionBuilder;

class ExpressionBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testAppendExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        ok($driver);

        $exprBuilder = new ExpressionBuilder;
        $exprBuilder->appendExpr('a', '=', 123);
        $sql = $exprBuilder->toSql($driver);
        is("a = 123",$sql);
    }

    public function testInExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ExpressionBuilder;
        $expr->in('b', [ 'a', 'b', 'c' ]);
        $sql = $expr->toSql($driver);
        is("b IN ('a','b','c')", $sql);
    }

    public function testNotInExpr() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ExpressionBuilder;
        $expr->notIn('z', [ 'a', 'b', 'c' ]);
        $sql = $expr->toSql($driver);
        is("z NOT IN ('a','b','c')", $sql);
    }

    public function testEqual() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ExpressionBuilder;
        $expr->equal('a', 1);
        $sql = $expr->toSql($driver);
        is("a = 1", $sql);
    }

    public function testNotEqual() {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr = new ExpressionBuilder;
        $expr->notEqual('a', 1);
        $sql = $expr->toSql($driver);
        is("a <> 1", $sql);
    }

    public function testExpressionBuilder()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $exprBuilder = new ExpressionBuilder;
        $exprBuilder->in('b', [ 'a', 'b', 'c' ]);
        $exprBuilder->notIn('z', [ 'a', 'b', 'c' ]);
        $exprBuilder->between('created_at', date('c') , date('c', time() + 3600));
        echo $exprBuilder->toSql($driver);
    }
}

