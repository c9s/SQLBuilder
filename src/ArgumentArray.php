<?php

namespace SQLBuilder;

use ArrayObject;

class ArgumentArray extends ArrayObject
{
    /**
     * @var Bind[]
     */
    protected $bindings = array();

    public function push(Bind $bind)
    {
        $this->bindings[] = $bind;
        $this[$bind->getMarker()] = $bind->getValue();
    }

    // Deprecated 
    public function add($bind)
    {
        // trigger_error("Bind::add is deprecated, please use Bind::bind instead.", E_USER_DEPRECATED);
        $this->bindings[] = $bind;
        $this[$bind->getMarker()] = $bind->getValue();
    }

    public function bind($bind)
    {
        $this->bindings[] = $bind;
        $this[$bind->getMarker()] = $bind->getValue();
    }

    public function getBindingByIndex($idx)
    {
        return $this->bindings[$idx];
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    public function getArgs()
    {
        return $this->getArrayCopy();
    }

    /**
     * toArray returns an array of the current arguments.
     *
     * Set $removeBinds to true if you want this array to be passed to PDO statement.
     *
     * @param bool $removeBinds
     */
    public function toArray($removeBinds = false)
    {
        if ($removeBinds) {
            $args = array();
            foreach ($this as $key => $val) {
                $args[$key] = $val instanceof Bind ? $val->getValue() : $val;
            }

            return $args;
        }

        return $this->getArrayCopy();
    }
}
