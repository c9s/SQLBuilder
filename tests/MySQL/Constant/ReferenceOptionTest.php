<?php

class ReferenceOptionTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertEquals('RESTRICT', SQLBuilder\MySQL\Constant\ReferenceOption::RESTRICT );
    }
}

