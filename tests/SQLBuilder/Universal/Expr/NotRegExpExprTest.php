<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\NotRegExpExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class NotRegExpExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new NotRegExpExpr('a', '[[:alnum:]]+');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "a NOT REGEXP '[[:alnum:]]+'"],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver, "`a` NOT REGEXP '[[:alnum:]]+'"],
        ]);
    }
}
