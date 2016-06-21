<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\NotInExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class NotInExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new NotInExpr('a', [1, 'a']);
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "a NOT IN (1,'a')"],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver, "`a` NOT IN (1,'a')"],
        ]);
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorInvalidType() {
        $expr = new NotInExpr('a', true);
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "a NOT IN (1,'a')"],
        ]);
    }
}
