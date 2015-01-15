<?php
use SQLBuilder\Bind;

class BindTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $bind = new Bind('name', 'Mary');
        ok($bind);
        $bind->setValue('Hacker');
        is('Hacker', $bind->getValue());

        is('name', $bind->getName());
        is(':name', $bind->getMark());
    }
}

