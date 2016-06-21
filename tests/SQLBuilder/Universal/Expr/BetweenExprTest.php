<?php

use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Expr\BetweenExpr;
use SQLBuilder\Testing\QueryTestCase;

class BetweenExprTest extends QueryTestCase
{
    public function testConstructor()
    {
        $expr = new BetweenExpr('age', 12, 20);

        $driver = new MySQLDriver;
        $this->assertSqlStrings($expr,[
            [$driver,'age BETWEEN 12 AND 20'],
        ]);

        // quote test
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($expr,[
            [$driver,'`age` BETWEEN 12 AND 20'],
        ]);
    }
    public function testBetweenExprVarExport()
    {
        $expr = new BetweenExpr('age', 12, 20);
        $code = 'return ' . var_export($expr, true) . ';';
        $ret = eval($code); 
        $this->assertInstanceOf('SQLBuilder\Universal\Expr\BetweenExpr', $ret);
    }
}

