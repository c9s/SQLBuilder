<?php

class ExpressionTest extends PHPUnit_Framework_TestCase
{

    public function createExpr()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $driver->setNoParamMarker();

        $expr = new SQLBuilder\Expression;
        $expr->driver = $driver;
        $expr->builder = new SQLBuilder\QueryBuilder($driver);
        return $expr;
    }


    public function testInExpression()
    {
        $expr = $this->createExpr();
        $expr->in( 'a' , array(1,2,3,4));
        is( 'a IN (1, 2, 3, 4)', $expr->toSql() );
    }


    public function testOpIs()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )->is( 'b' , 'null' );
        is( 'a is null AND b is null', $expr->toSql() );
    }

    public function testOpIsNot()
    {
        $expr = $this->createExpr();
        $expr->isNot( 'a' , 'null' )->isNot( 'b' , 'null' );
        is( 'a is not null AND b is not null', $expr->toSql() );
    }

    public function testOpIsNot2()
    {
        $expr = $this->createExpr();
        $expr->isNot( 'a' , 'true' )->isNot( 'b' , 'true' );
        is( 'a is not true AND b is not true', $expr->toSql() );
    }

    public function testOpAnd()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )
            ->and()->is( 'b', 'null' );
        is( 'a is null AND b is null', $expr->toSql() );
    }

    public function testOpOr()
    {
        $expr = $this->createExpr();
        $expr->is( 'a' , 'null' )
            ->or()->is( 'b', 'null' );
        is( 'a is null OR b is null', $expr->toSql() );
    }

    public function testOpEqual()
    {
        $expr = $this->createExpr();
        $expr->equal( 'a' , 'foo' )
            ->or()->equal( 'b', 'bar' );
        is( "a = 'foo' OR b = 'bar'", $expr->toSql() );
    }

    public function testOpWithSqlFunction()
    {
        $expr = $this->createExpr();
        $expr->equal( 'a' , array("format('2011-12-11')") )
            ->or()->equal( 'b', 'bar' );
        is( "a = format('2011-12-11') OR b = 'bar'", $expr->toSql() );
    }

    public function testLike()
    {
        $expr = $this->createExpr();
        $expr->like( 'content' , '%aaa%' );
        is( "content like '%aaa%'", $expr->toSql() );
    }

    public function testToString()
    {
        $expr = $this->createExpr();
        $expr->like( 'content' , '%aaa%' );
        $backExpr = $expr->group()
                ->equal( 'a' , 'b' )
                ->equal( 'c' , 'd' )
            ->ungroup()
            ->or()->equal( 'name' , 'foo' )
            ->or()->equal( 'name' , 'bar' )
            ->back();
        is( $backExpr , $expr );
        is( "content like '%aaa%' AND (a = 'b' AND c = 'd') OR name = 'foo' OR name = 'bar'", $expr . '' );

    }

    public function testBack()
    {
        $expr = $this->createExpr();
        $expr->like( 'content' , '%aaa%' );
        $sql = $expr->group()
            ->equal( 'a' , 'b' )
            ->equal( 'c' , 'd' )
            ->ungroup()
            ->or()->equal( 'name' , 'foo' )
            ->or()->equal( 'name' , 'bar' )
            ->back()->toSql();
        is( "content like '%aaa%' AND (a = 'b' AND c = 'd') OR name = 'foo' OR name = 'bar'", $sql );
    }

    public function testGroup()
    {
        $expr = $this->createExpr();
        $expr->like( 'content' , '%aaa%' );
        $expr->group()
            ->equal( 'a' , 'b' )
            ->equal( 'c' , 'd' )
            ->ungroup()
                ->and()->is( 'name' , 'null' );
        is( "content like '%aaa%' AND (a = 'b' AND c = 'd') AND name is null", $expr->toSql() );
    }

    public function testSimpleGroup()
    {
        $expr = $this->createExpr();
        $expr->group()
            ->equal( 'a' , 'b' )
            ->equal( 'c' , 'd' )
            ->ungroup();
        is( " (a = 'b' AND c = 'd')", $expr->toSql() );
    }

    public function testDoubleGroup()
    {
        $expr = $this->createExpr();
        $expr->group()
                ->equal( 'a' , 1 )
                ->equal( 'b' , 2 )
            ->ungroup()
            ->group()
                ->equal( 'c' , 'c' )
                ->equal( 'd' , 'd' )
            ->ungroup();
        is( " (a = 1 AND b = 2) AND (c = 'c' AND d = 'd')", $expr->toSql() );
    }

    public function testGroup2()
    {
        $expr = $this->createExpr();
        $expr->like( 'content' , '%aaa%' );
        $expr->group()
                ->equal( 'a' , 'b' )
                ->equal( 'c' , 'd' )
            ->ungroup()
            ->group('OR')
                ->equal( 'name' , 'Mary' )
                ->equal( 'address' , 'Taipei' )
            ->ungroup();
        is( "content like '%aaa%' AND (a = 'b' AND c = 'd') OR (name = 'Mary' AND address = 'Taipei')", $expr->toSql() );
    }

    public function testGreater()
    {
        $expr = $this->createExpr();
        $expr->greater( 'a', 123 );
        is( "a > 123", $expr->toSql() );
    }

    public function testGreaterWithString()
    {
        $expr = $this->createExpr();
        $expr->greater( 'date', '2011-01-01' );
        is( "date > '2011-01-01'", $expr->toSql() );
    }

    public function testGreaterWithSqlFunction()
    {
        $expr = $this->createExpr();
        $expr->greater( 'date', array("format('2011-01-01')") );
        is( "date > format('2011-01-01')", $expr->toSql() );
    }

    public function testBetweenDate()
    {
        $expr = $this->createExpr();
        $expr->between('created_on','2011-01-01','2012-01-01');
        is( "created_on BETWEEN '2011-01-01' AND '2012-01-01'" , $expr->toSql() );
    }


    public function testBetweenNumber()
    {
        $expr = $this->createExpr();
        $expr->between('created_on', 10, 20);
        is( "created_on BETWEEN 10 AND 20" , $expr->toSql() );
    }

    public function testBetweenWithOtherExpression()
    {
        $expr = $this->createExpr();
        $expr->equal('foo', 1)
             ->between('id', 10, 20);
        is( "foo = 1 AND id BETWEEN 10 AND 20", $expr->toSql() );
    }

}
