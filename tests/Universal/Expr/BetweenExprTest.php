<?php
use SQLBuilder\Universal\Expr\BetweenExpr;

class BetweenExprTest extends PHPUnit_Framework_TestCase
{
    public function testBetweenExprVarExport()
    {
        $expr = new BetweenExpr('age', 12, 20);
        $code = 'return ' . var_export($expr, true) . ';';
        $ret = eval($code); 
        $this->assertInstanceOf('SQLBuilder\Universal\Expr\BetweenExpr', $ret);
    }
}

