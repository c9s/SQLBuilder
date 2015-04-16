<?php
namespace SQLBuilder;
use BadMethodCallException;
use ReflectionObject;
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
 *
 *
 */
trait SyntaxExtender {

    protected $extraSyntax = array();

    protected $syntaxClass = array();

    protected $reflectionCache = array();

    public function registerClass($methodName, $class) 
    {
        $this->syntaxClass[ $methodName ] = $class;
    }

    public function registerCallback($methodName, callable $callback) 
    {
        $this->extraSyntax[$methodName] = $callback;
    }


    public function handleSyntax($methodName, array $arguments = array())
    {
        if (isset($this->syntaxClass[$methodName])) {
            $refClass = null;
            if (isset($this->reflectionCache[$methodName])) {
                $refClass = $this->reflectionCache[$methodName];
            } else {
                $this->reflectionCache[$methodName] = $refClass = new ReflectionClass($this->syntaxClass[$methodName]);
            }
            return $refClass->newInstanceArgs($arguments);
        } else if (isset($this->extraSyntax[$methodName])) {
            return call_user_func_array($this->extraSyntax[$methodName], $arguments);
        } else {
            throw new BadMethodCallException;
        }
    }
}

