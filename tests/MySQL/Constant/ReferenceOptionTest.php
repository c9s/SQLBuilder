<?php

class ReferenceOptionTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->assertEquals('RESTRICT', SQLBuilder\MySQL\Constant\ReferenceOption::RESTRICT );
    }
}

