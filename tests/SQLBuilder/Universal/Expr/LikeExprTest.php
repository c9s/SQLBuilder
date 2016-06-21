<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\LikeExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class LikeExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new LikeExpr('a', 'b');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "a LIKE '%b%'"],
        ]);

        // quote test
        $expr = new LikeExpr('a', 'b');
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver, "`a` LIKE '%b%'"],
        ]);
    }
}
