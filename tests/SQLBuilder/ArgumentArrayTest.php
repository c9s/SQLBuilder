<?php
use SQLBuilder\Bind;
use SQLBuilder\ArgumentArray;

class ArgumentArrayTest extends PHPUnit_Framework_TestCase
{
    public function testArguments()
    {
        $args = new ArgumentArray;
        $args->add(new Bind('name', 'John'));
        ok($args);

        is('John', $args[':name']);

        ok($args instanceof IteratorAggregate);
    }
}

