<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\ExplainQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Expr\ListExpr;
use SQLBuilder\Bind;
use SQLBuilder\Raw;

class ListExprTest extends QueryTestCase
{
    public function createDriver() {
        return new MySQLDriver;
    }

    public function testStringListExpr()
    {
        $expr = new ListExpr('1,2,3');
        $this->assertSql('(1,2,3)', $expr);
    }

    public function testRaw()
    {
        $expr = new ListExpr(new Raw('1,2,3'));
        $this->assertSql('(1,2,3)', $expr);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnknownType()
    {
        $expr = new ListExpr(1);
        $this->assertSql('(1,2,3)', $expr);
    }




}

