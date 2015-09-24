<?php
namespace SQLBuilder;
use ArrayIterator;
use ArrayAccess;
use SQLBuilder\Bind;
use IteratorAggregate;

class ArgumentArray implements ArrayAccess, IteratorAggregate
{

    /**
     * @var array
     *
     *   {
     *      :name => 'John',
     *      :phone => 'Phone',
     *   }
     *
     */
    protected $args = array();

    /**
     * @var Bind[]
     */
    protected $bindings = array();


    public function __construct(array $args = array())
    {
        $this->args = $args;
    }


    public function getIterator()
    {
        return new ArrayIterator($this->args);
    }

    public function add(Bind $bind) 
    {
        $this->bindings[] = $bind;
        $this->args[$bind->getMarker()] = $bind->getValue();
    }

    public function getBindingByIndex($idx)
    {
        return $this->bindings[$idx];
    }

    public function getBindings() {
        return $this->bindings;
    }

    public function offsetSet($name, $value)
    {
        $this->args[$name] = $value;
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

    public function getArgs()
    {
        return $this->args;
    }

    
    /**
     * toArray returns an array of the current arguments.
     *
     * Set $removeBinds to true if you want this array to be passed to PDO statement.
     *
     * @param boolean $removeBinds 
     */
    public function toArray($removeBinds = false)
    {
        if ($removeBinds) {
            $args = array();
            foreach($this->args as $key => $val) {
                $args[$key] = $val instanceof Bind ? $val->getValue() : $val;
            }
            return $args;
        }
        return $this->args;
    }
}



