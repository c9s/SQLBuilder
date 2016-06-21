<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\RegExpExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class RegExpExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new RegExpExpr('a', '[[:alnum:]]+');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "a REGEXP '[[:alnum:]]+'"],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver, "`a` REGEXP '[[:alnum:]]+'"],
        ]);
    }
}
