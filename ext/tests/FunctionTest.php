<?php

class FunctionTest extends PHPUnit_Framework_TestCase
{
    public function testSingleQuote()
    {
        $val = sqlbuilder_single_quote('name');
        is("'name'",$val);
    }

    public function testDoubleQuote()
    {
        $val = sqlbuilder_double_quote('name');
        is("\"name\"",$val);
    }
}

