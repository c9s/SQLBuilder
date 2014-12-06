<?php
use SQLBuilder\Bind;
use SQLBuilder\Arguments;

class ArgumentsTest extends PHPUnit_Framework_TestCase
{
    public function testArguments()
    {
        $args = new Arguments;
        $args->add(new Bind('name', 'John') );
        ok($args);

        is('John', $args[':name']);

        ok($args instanceof IteratorAggregate);
    }
}

