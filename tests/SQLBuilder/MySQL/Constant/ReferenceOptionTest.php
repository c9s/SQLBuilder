<?php

class ReferenceOptionTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        is('RESTRICT', SQLBuilder\MySQL\Constant\ReferenceOption::RESTRICT );
    }
}

