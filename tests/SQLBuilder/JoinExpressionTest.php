<?php

class JoinExpressionTest extends PHPUnit_Framework_TestCase
{
    public function createExpr()
    {
        $expr = new SQLBuilder\JoinExpression('users');
        $expr->driver = new SQLBuilder\Driver;
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
            ->equal( 'a' , array('a') )
            ->equal( 'c' , array('c') );

        $sql = $expr->toSql();
        is(" LEFT JOIN users u ON (a = a AND c = c)" , $sql );
    }
}

