<?php
use SQLBuilder\Universal\Expr\BinaryExpr;

class BinaryExprTest extends PHPUnit_Framework_TestCase
{
    public function testBinaryExprVarExport()
    {
        $expr = new BinaryExpr(1, '+', 20);
        $code = 'return ' . var_export($expr, true) . ';';
        $ret = eval($code); 
        $this->assertInstanceOf('SQLBuilder\Universal\Expr\BinaryExpr', $ret);
        $this->assertEquals('+', $ret->op);
    }
}

