<?php

use SQLBuilder\Raw;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Bind;
use SQLBuilder\Testing\QueryTestCase;

class FuncCallExprTest extends QueryTestCase
{
    public function testFuncCall()
    {
        $driver = new MySQLDriver;
        $func = new FuncCallExpr('COUNT', [ new Raw('*') ]);
        $this->assertSqlStrings($func,[
            [$driver, 'COUNT(*)'],
        ]);

        $func = new FuncCallExpr('COUNT', [ new Raw('*') ]);
        $driver = new MySQLDriver;
        $driver->quoteColumn = true;
        $this->assertSqlStrings($func,[
            [$driver, 'COUNT(*)'],
        ]);
    }
}