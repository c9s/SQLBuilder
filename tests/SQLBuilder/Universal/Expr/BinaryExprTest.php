<?php

use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Expr\BinaryExpr;
use SQLBuilder\Testing\QueryTestCase;

class BinaryExprTest extends QueryTestCase
{
    public function testConstructor()
    {
        $expr = new BinaryExpr(1, '+', 20);

        $driver = new MySQLDriver;
        $this->assertSqlStrings($expr,[
            [$driver,'1 + 20'],
        ]);
    }
    public function testBinaryExprVarExport()
    {
        $expr = new BinaryExpr(1, '+', 20);
        $code = 'return ' . var_export($expr, true) . ';';
        $ret = eval($code); 
        $this->assertInstanceOf('SQLBuilder\Universal\Expr\BinaryExpr', $ret);
        $this->assertEquals('+', $ret->op);
    }
}

