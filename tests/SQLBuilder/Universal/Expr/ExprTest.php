<?php

use SQLBuilder\Universal\Expr\Expr;

class ExprTest extends PHPUnit_Framework_TestCase
{
    function testConstructor()
    {
        $expr = new Expr;
        $this->assertEquals(true, $expr instanceof Expr);
    }
}