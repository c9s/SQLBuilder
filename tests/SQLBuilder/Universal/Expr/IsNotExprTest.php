<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\IsNotExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class IsNotExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new IsNotExpr('a', true);
        $this->assertSqlStrings($expr,[
            [new MySQLDriver,'a IS NOT TRUE'],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver,'`a` IS NOT TRUE'],
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorInvalidType() {
        $expr = new IsNotExpr('a', 'blah');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver,'a IS NOT TRUE'],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver,'`a` IS NOT TRUE'],
        ]);
    }
}
