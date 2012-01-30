<?php

class ExpressionTest extends PHPUnit_Framework_TestCase
{

    public function createExpr()
    {
        $expr = new SQLBuilder\Expression;
        $expr->driver = new SQLBuilder\Driver;
        return $expr;
    }

    public function testOp()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )->is( 'b' , 'null' );
        is( 'a is null AND b is null', $expr->inflate() );
    }

    public function testOp2()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )
            ->and()->is( 'b', 'null' );
        is( 'a is null AND b is null', $expr->inflate() );
    }


    public function testOp3()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )
            ->or()->is( 'b', 'null' );
        is( 'a is null OR b is null', $expr->inflate() );
    }

    public function testOp4()
    {
        $expr = $this->createExpr();
        $expr->isEqual( 'a' , 'foo' )
            ->or()->isEqual( 'b', 'bar' );
        is( "a = 'foo' OR b = 'bar'", $expr->inflate() );
    }

    public function testOp5()
    {
        $expr = $this->createExpr();
        $expr->isEqual( 'a' , array("format('2011-12-11')") )
            ->or()->isEqual( 'b', 'bar' );
        is( "a = format('2011-12-11') OR b = 'bar'", $expr->inflate() );
    }


}

