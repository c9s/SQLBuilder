<?php
namespace SQLBuilder;
use Exception;

trait Accessor { 

    protected $accessorHandlers = array();

    protected $properties = array();

    public function defineAccessor($name, $handler) {
        $this->accessorHandlers[$name] = $handler;
    }

    public function __call($method, $args) {
        if (isset($this->accessorHandlers[$method])) {
            if (count($args) == 0) {
                $this->properties[$method] = true;
            } elseif (count($args) == 1) {
                $this->properties[$method] = $args[0];
            } elseif (count($args) > 1) {
                $this->properties[$method] = $args;
            }
            return $this;
        }

        if (preg_match('/^build(\w+?)Clause$/', $method, $regs)) {
            $name = strtolower($regs[0]);
            if (isset($this->accessorHandlers[$name])) {
                $val = isset($this->properties[$name]) ? $this->properties[$name] : NULL;
                if (is_callable($this->accessorHandlers[$name])) {
                    return call_user_func($this->accessorHandlers[$name], array($args[0], $args[1], $val));
                } elseif (is_string($this->accessorHandlers[$name])) {
                    if (isset($this->properties[$name])) {
                        return ' ' . $this->accessorHandlers[$name];
                    } else {
                        return '';
                    }
                } else {
                    throw new Exception('Unsupported type');
                }
            }
        }
        throw new Exception("Invalid property $method");
    }

}


