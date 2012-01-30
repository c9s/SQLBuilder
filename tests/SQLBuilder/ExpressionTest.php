<?php

class ExpressionTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $builder = new SQLBuilder\CRUDBuilder('Foo');
        $builder->driver = new SQLBuilder\Driver;

        $expr = new SQLBuilder\Expression;
        $expr->builder = $builder;
        $expr->is( 'a' , 'b' )->is( 'b' , 'c' );
        var_dump( $expr->inflate() );
        
    }
}

