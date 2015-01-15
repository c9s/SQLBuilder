<?php
use SQLBuilder\Bind;
use SQLBuilder\ArgumentArray;

class ArgumentArrayTest extends PHPUnit_Framework_TestCase
{
    public function testAddArgument()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        ok($args);
        is('John', $args[':name']);
        ok($args instanceof IteratorAggregate);
    }

    public function testUnsetArgument()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        $args->add(new Bind('foo', 'FooBar'));
        ok( isset($args[':foo']) );

        $foo = $args[':foo'];
        is('FooBar', $foo);

        unset( $args[':foo'] );

        ok(is_array($args->toArray()));
        ok(is_array($args->getArgs()));

        foreach($args->getBindings() as $binding) {
            ok($binding instanceof Bind);
        }
    }

    public function testSettter() {
        $args = new ArgumentArray;
        $args[':name'] = 'John';
        $args[':foo'] = 'FooBar';

        ok( isset($args[':foo']) );
        ok( isset($args[':name']) );
    }

    public function testIterating() {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        $args->add(new Bind('foo', 'FooBar'));
        foreach($args as $n => $v) {
            ok( is_string($n) );
            ok( in_array($v, array('John', 'FooBar')));
        }
    }



}

