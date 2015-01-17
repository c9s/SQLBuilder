<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Bind;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\ExplainQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Syntax\SelectAs;
use SQLBuilder\ANSI\AggregateFunction;

class SelectAsTest extends PHPUnit_Framework_TestCase
{
    public function testString()
    {
        $expr = new SelectAs('products', 'p');
        $sql = $expr->toSql( new MySQLDriver, new ArgumentArray);
        ok($sql);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnknownTypeExpr()
    {
        $expr = new SelectAs(TRUE, 'p');
        $sql = $expr->toSql( new MySQLDriver, new ArgumentArray);
    }


    public function testFuncExpr() {
        $expr = new SelectAs(AggregateFunction::COUNT('*'), 'a');
        $sql = $expr->toSql( new MySQLDriver, new ArgumentArray);
        is('COUNT(*) AS `a`', $sql);
    }
}

