<?php
use SQLBuilder\Bind;

class BindTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $bind = new Bind('name', 'Mary');
        $bind->setValue('Hacker');
        $this->assertEquals('Hacker', $bind->getValue());

        $this->assertEquals('name', $bind->getName());
        $this->assertEquals(':name', $bind->getMarker());
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

