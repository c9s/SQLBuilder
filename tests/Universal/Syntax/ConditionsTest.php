<?php
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\Criteria;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Universal\Syntax\Conditions;

class ConditionsTest extends QueryTestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCall()
    {
        $expr = new Conditions;
        $expr->foo();
    }

    public function testRawExpr()
    {
        $conds = new Conditions;
        $conds->append(new Raw('1 + 1'));
        $conds->append(new Raw('2 + 2'));
        $this->assertSqlStrings($conds, [
            [new MySQLDriver, '1 + 1 AND 2 + 2']
        ]);
    }

    public function testAppendExpr()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        ok($driver);

        $exprBuilder = new Conditions;
        $exprBuilder->appendBinExpr('a', '=', 123);
        $sql = $exprBuilder->toSql($driver, $args);
        is("a = 123", $sql);
    }

    public function testInExpr()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->in('b', ['a', 'b', 'c']);
        $sql = $expr->toSql($driver, $args);
        is("b IN ('a','b','c')", $sql);
    }


    public function testNotInExpr()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->notIn('z', ['a', 'b', 'c']);
        $sql = $expr->toSql($driver, $args);
        is("z NOT IN ('a','b','c')", $sql);
    }

    public function testEqual()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->equal('a', 1);
        $sql = $expr->toSql($driver, $args);
        is("a = 1", $sql);
    }


    public function testLessThan()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->lessThan('view', 100);
        $sql = $expr->toSql($driver, $args);
        is("view < 100", $sql);
    }

    public function testLessThanOrEqual()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->lessThanOrEqual('view', 100);
        $sql = $expr->toSql($driver, $args);
        is("view <= 100", $sql);
    }

    public function testGreaterThan()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->greaterThan('view', 100);
        $sql = $expr->toSql($driver, $args);
        is("view > 100", $sql);
    }

    public function testGreaterThanOrEqual()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->greaterThanOrEqual('view', 100);
        $sql = $expr->toSql($driver, $args);
        is("view >= 100", $sql);
    }

    public function testNotEqual()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->notEqual('a', 1);
        $sql = $expr->toSql($driver, $args);
        is("a <> 1", $sql);
    }

    public function testIsNot()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $expr = new Conditions;
        $expr->isNot('is_book', true);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS NOT TRUE", $sql);


        $args = new ArgumentArray;
        $expr = new Conditions;
        $expr->isNot('is_book', false);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS NOT FALSE", $sql);

        $args = new ArgumentArray;
        $expr = new Conditions;
        $expr->isNot('is_book', new Unknown);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS NOT UNKNOWN", $sql);
    }

    public function testOperatorMethod()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $conditions = new Conditions;
        $conditions->is('confirmed', true)
            ->or()->is('approved', true)
            ->and()->equal('points', 100);
        $sql = $conditions->toSql($driver, $args);
        is("confirmed IS TRUE OR approved IS TRUE AND points = 100", $sql);
    }


    public function testOperatorXor()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $conditions = new Conditions;
        $conditions->is('confirmed', true)
            ->xor()->is('approved', true);
        $sql = $conditions->toSql($driver, $args);
        is("confirmed IS TRUE XOR approved IS TRUE", $sql);
    }


    public function testConditionGroup()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $conditions = new Conditions;
        $conditions->is('confirmed', true)
            ->or()->is('approved', true)
            ->group()
            ->like('name', 'John')
            ->or()
            ->like('name', 'Mary')
            ->endgroup();
        $sql = $conditions->toSql($driver, $args);
        is("confirmed IS TRUE OR approved IS TRUE AND (name LIKE '%John%' OR name LIKE '%Mary%')", $sql);
    }

    public function testIs()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;

        $expr = new Conditions;
        $expr->is('is_book', true);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS TRUE", $sql);

        $args = new ArgumentArray;
        $expr = new Conditions;
        $expr->is('is_book', false);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS FALSE", $sql);

        $args = new ArgumentArray;
        $expr = new Conditions;
        $expr->is('is_book', new Unknown);
        $sql = $expr->toSql($driver, $args);
        is("is_book IS UNKNOWN", $sql);
    }


    public function likeExprProvider()
    {
        return [
            [null, "John", "name LIKE '%John%'"],
            [Criteria::CONTAINS, "John", "name LIKE '%John%'"],
            [Criteria::STARTS_WITH, "John", "name LIKE 'John%'"],
            [Criteria::ENDS_WITH, "John", "name LIKE '%John'"],
            [Criteria::EXACT, "John", "name LIKE 'John'"],
        ];
    }


    /**
     * @dataProvider likeExprProvider
     */
    public function testLikeExpr($criteria, $pat, $expectedSql)
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->like('name', $pat, $criteria);
        $sql = $expr->toSql($driver, $args);
        is($expectedSql, $sql);


        $expr = new Conditions;
        $expr->like('name', new Bind('name', $pat), $criteria);
        $sql = $expr->toSql($driver, $args);
        is('name LIKE :name', $sql);

        ok($expr->notEmpty());
    }

    public function testRegExp()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->regExp('content', '.*');
        $sql = $expr->toSql($driver, $args);
        is("content REGEXP '.*'", $sql);

        ok($expr->notEmpty());
    }

    public function testCompareSameEqualExprWithBind()
    {
        $expr1 = new Conditions;
        $expr1->equal('foo', new Bind('foo', 1));
        $expr1->equal('bar', new Bind('bar', 2));

        $expr2 = new Conditions;
        $expr2->equal('foo', new Bind('foo', 1));
        $expr2->equal('bar', new Bind('bar', 2));

        $this->assertFalse($expr1->compare($expr2));
    }

    public function testCompareDifferentEqualExprWithBind()
    {
        $expr1 = new Conditions;
        $expr1->equal('foo', new Bind('foo', 0));
        $expr1->equal('bar', new Bind('bar', 1));

        $expr2 = new Conditions;
        $expr2->equal('foo', new Bind('foo', 0));
        $expr2->equal('bar', new Bind('bar', 3));

        $this->assertFalse($expr1->compare($expr2));
    }


    public function testCompareDifferentEqualExpr()
    {
        $expr1 = new Conditions;
        $expr1->equal('foo', 0);
        $expr1->equal('bar', 1);

        $expr2 = new Conditions;
        $expr2->equal('foo', 0);
        $expr2->equal('bar', 3);

        $this->assertFalse($expr1->compare($expr2));
    }

    public function testCompareSameEqualExpr()
    {
        $expr1 = new Conditions;
        $expr1->equal('foo', 1);
        $expr1->equal('bar', 1);

        $expr2 = new Conditions;
        $expr2->equal('foo', 1);
        $expr2->equal('bar', 1);

        $this->assertTrue($expr1->compare($expr2));
    }

    public function testNotRegExp()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->notRegExp('content', '.*');
        $sql = $expr->toSql($driver, $args);
        is("content NOT REGEXP '.*'", $sql);
    }

    public function testBetweenExprWithDateTime()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->between('created_at', new DateTime, new DateTime);
        $sql = $expr->toSql($driver, $args);
        // var_dump($sql);

    }

    public function testBetweenExpr()
    {
        $args   = new ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $expr   = new Conditions;
        $expr->between('created_at', date('c'), date('c', time() + 3600));
        $sql = $expr->toSql($driver, $args);
        // var_dump($sql);
        // is("", $sql);
    }

    public function testVarExport()
    {
        $expr = new Conditions;
        $expr->regExp('content', '.*');
        $code = '$ret =     ' . var_export($expr, true) . ';';
        eval($code);
        $this->assertNotEmpty($ret);
        $this->assertInstanceOf('SQLBuilder\Universal\Syntax\Conditions', $ret);
    }
}

