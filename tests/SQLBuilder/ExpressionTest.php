<?php

class ExpressionTest extends PHPUnit_Framework_TestCase
{

    function createExpr()
    {
        $builder = new SQLBuilder\CRUDBuilder('Foo');
        $builder->driver = new SQLBuilder\Driver;
        $expr = new SQLBuilder\Expression;
        $expr->builder = $builder;
        return $expr;
    }

    function test()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )->is( 'b' , 'null' );
        is( 'a is null AND b is null', $expr->inflate() );

    }

    function test2()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )
            ->and()->is( 'b', 'null' );
        is( 'a is null AND b is null', $expr->inflate() );
    }
}

