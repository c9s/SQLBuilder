<?php
use SQLBuilder\Bind;
use SQLBuilder\ArgumentArray;

class ArgumentArrayTest extends \PHPUnit\Framework\TestCase
{
    public function testAddArgument()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        $this->assertEquals('John', $args[':name']);
        $this->assertInstanceOf('IteratorAggregate', $args);
    }

    public function testUnsetArgument()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        $args->add(new Bind('foo', 'FooBar'));
        $this->assertTrue(isset($args[':foo']));

        $foo = $args[':foo'];
        $this->assertEquals('FooBar', $foo);

        unset( $args[':foo'] );

        $this->assertTrue(is_array($args->toArray()));
        $this->assertTrue(is_array($args->getArgs()));
        foreach ($args->getBindings() as $binding) {
            $this->assertTrue($binding instanceof Bind);
        }
    }

    public function testSettter()
    {
        $args = new ArgumentArray;
        $args[':name'] = 'John';
        $args[':foo'] = 'FooBar';
        $this->assertTrue(isset($args[':foo']) );
        $this->assertTrue(isset($args[':name']) );
    }

    public function testIterating()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        $args->add(new Bind('foo', 'FooBar'));
        foreach($args as $n => $v) {
            $this->assertTrue(is_string($n));
            $this->assertTrue(in_array($v, array('John', 'FooBar')));
        }
    }



}

