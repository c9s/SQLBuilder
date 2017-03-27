<?php

namespace SQLBuilder;

use BadMethodCallException;
use ReflectionClass;

/**
 * SyntaxExtender let you register a customized method name to a syntax class...
 *
 *
 * Add the code below to support syntax extender:
 *
 *
 * class FooQuery {
 *
 *   use SQLBuilder\SyntaxExtender;
 *
 *   public function __call($methodName, $arguments = array()) {
 *     return $this->someProperty = $this->handleSyntax($methodName, $arguments);
 *   }
 *
 * }
 */
trait SyntaxExtender
{
    protected $extraSyntax = [];

    protected $syntaxClass = [];

    protected $reflectionCache = [];

    /**
     * @param string $methodName
     * @param        $class
     */
    public function registerClass($methodName, $class)
    {
        $this->syntaxClass[$methodName] = $class;
    }

    /**
     * @param string   $methodName
     * @param callable $callback
     */
    public function registerCallback($methodName, callable $callback)
    {
        $this->extraSyntax[$methodName] = $callback;
    }

    /**
     * @param       $methodName
     * @param array $arguments
     *
     * @return mixed|object
     * @throws \BadMethodCallException
     */
    public function handleSyntax($methodName, array $arguments = [])
    {
        if (isset($this->syntaxClass[$methodName])) {
            $refClass = null;
            if (isset($this->reflectionCache[$methodName])) {
                $refClass = $this->reflectionCache[$methodName];
            } else {
                $this->reflectionCache[$methodName] = $refClass = new ReflectionClass($this->syntaxClass[$methodName]);
            }

            return $refClass->newInstanceArgs($arguments);
        }

        if (isset($this->extraSyntax[$methodName])) {
            return call_user_func_array($this->extraSyntax[$methodName], $arguments);
        }

        throw new BadMethodCallException();
    }
}
