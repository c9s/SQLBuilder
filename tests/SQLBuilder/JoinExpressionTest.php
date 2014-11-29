<?php

class JoinExpressionTest extends PHPUnit_Framework_TestCase
{
    public function createExpr()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $expr = new SQLBuilder\JoinExpression('users');
        $expr->driver = $driver;
        $expr->builder = new SQLBuilder\QueryBuilder($driver);

        return $expr;
    }

    public function test()
    {
        $expr = $this->createExpr();
        ok( $expr );

        $expr->alias('u')
            ->on()
            ->equal( 'a' , array('b') );

        $sql = $expr->toSql();
        is(" LEFT JOIN users u ON (a = b)" , $sql );
    }

    public function test2()
    {
        $expr = $this->createExpr();
        ok( $expr );

        $expr->alias('u')
            ->on()
                ->equal( 'a' , array('a') )->back()
            ->on()
                ->equal( 'b' , array('b') );

        $sql = $expr->toSql();
        is(" LEFT JOIN users u ON (a = a) ON (b = b)" , $sql );
    }
}

