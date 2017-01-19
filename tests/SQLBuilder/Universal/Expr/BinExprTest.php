<?php
use SQLBuilder\Universal\Expr\BinExpr;

class BinExprTest extends PHPUnit_Framework_TestCase
{
    public function testBinExprVarExport()
    {
        $expr = new BinExpr(1, '+', 20);
        $code = 'return ' . var_export($expr, true) . ';';
        $ret = eval($code); 
        $this->assertInstanceOf('SQLBuilder\Universal\Expr\BinExpr', $ret);
        $this->assertEquals('+', $ret->op);
    }
}

