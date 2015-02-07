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
        is(':name', $bind->getMarker());
    }


    public function testBindArray()
    {
        $array = Bind::bindArray(array(
            'name' => 'John',
            'phone' => '123123',
        ));

        $this->assertInstanceOf('SQLBuilder\Bind', $array['name']);
        $this->assertInstanceOf('SQLBuilder\Bind', $array['phone']);
    }
}

