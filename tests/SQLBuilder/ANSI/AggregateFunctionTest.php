<?php
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\ANSI\AggregateFunction;
use SQLBuilder\Universal\Syntax\SelectAs;

class AggregateFunctionTest extends PHPUnit_Framework_TestCase
{

    public function functionProvider() {
        $args = array();
        $args[] = [ AggregateFunction::SUM(10) , 'SELECT SUM(10)' ];
        $args[] = [ AggregateFunction::SUM('total_amount') , 'SELECT SUM(total_amount)' ];
        $args[] = [ AggregateFunction::MAX('views') , 'SELECT MAX(views)' ];
        $args[] = [ AggregateFunction::AVG('buyPrice') , 'SELECT AVG(buyPrice)' ];
        $args[] = [ AggregateFunction::COUNT('*') , 'SELECT COUNT(*)' ];
        $args[] = [ new SelectAs(AggregateFunction::COUNT('*'), 'a'), 'SELECT COUNT(*) AS `a`'];
        return $args;
    }

    /**
     * @dataProvider functionProvider
     */
    public function testFunctions($func, $expectedSql)
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new SelectQuery;
        $query->select($func);
        $sql = $query->toSql($driver, $args);
        ok($sql);
        is($expectedSql,$sql);
    }

}

