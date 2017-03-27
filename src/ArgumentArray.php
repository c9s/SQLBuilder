<?php

namespace SQLBuilder;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class ArgumentArray
 *
 * @package SQLBuilder
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class ArgumentArray implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     *
     *   {
     *      :name => 'John',
     *      :phone => 'Phone',
     *   }
     */
    protected $args = [];

    /**
     * @var Bind[]
     */
    protected $bindings = [];

    /**
     * ArgumentArray constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->args);
    }

    /**
     * @param \SQLBuilder\Bind $bind
     */
    public function push(Bind $bind)
    {
        $this->bindings[]               = $bind;
        $this->args[$bind->getMarker()] = $bind->getValue();
    }

    /**
     * @param \SQLBuilder\Bind $bind
     *
     * @deprecated
     */
    public function add(Bind $bind)
    {
        // trigger_error("Bind::add is deprecated, please use Bind::bind instead.", E_USER_DEPRECATED);
        $this->bindings[]               = $bind;
        $this->args[$bind->getMarker()] = $bind->getValue();
    }

    /**
     * @param \SQLBuilder\Bind $bind
     */
    public function bind(Bind $bind)
    {
        $this->bindings[]               = $bind;
        $this->args[$bind->getMarker()] = $bind->getValue();
    }

    /**
     * @param $idx
     *
     * @return \SQLBuilder\Bind
     */
    public function getBindingByIndex($idx)
    {
        return $this->bindings[$idx];
    }

    /**
     * @return \SQLBuilder\Bind[]
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($name, $value)
    {
        $this->args[$name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($name)
    {
        return isset($this->args[$name]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($name)
    {
        return $this->args[$name];
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($name)
    {
        unset($this->args[$name]);
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * toArray returns an array of the current arguments.
     *
     * Set $removeBinds to true if you want this array to be passed to PDO statement.
     *
     * @param bool $removeBinds
     *
     * @return array
     */
    public function toArray($removeBinds = false)
    {
        if ($removeBinds) {
            $args = [];
            foreach ($this->args as $key => $val) {
                $args[$key] = $val instanceof Bind ? $val->getValue() : $val;
            }

            return $args;
        }

        return $this->args;
    }
}
