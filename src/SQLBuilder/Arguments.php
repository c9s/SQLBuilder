<?php
namespace SQLBuilder;
use ArrayIterator;
use ArrayAccess;
use SQlBuilder\Bind;
use IteratorAggregate;

class Arguments implements ArrayAccess, IteratorAggregate
{
    public $args = array();

    public $vars = array();

    public function getIterator() {
        return new ArrayIterator($this->args);
    }

    public function add(Bind $bind) { 
        $this->vars[] = $bind;
        $this->args[$bind->getMark()] = $bind->value;
    }

    public function offsetSet($name,$value)
    {
        $this->args[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->args[ $name ]);
    }
    
    public function offsetGet($name)
    {
        return $this->args[ $name ];
    }
    
    public function offsetUnset($name)
    {
        unset($this->args[$name]);
    }
    
}



