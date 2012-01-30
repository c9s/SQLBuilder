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
            ->on()->equal( 'a' , array('b') );

        $sql = $expr->inflate();
        is(" LEFT JOIN users u ON a = b" , $sql );
    }
}

