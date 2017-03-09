<?php

namespace SQLBuilder;

class Bind
{
    protected $name;

    protected $value;

    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMarker()
    {
        return ':'.$this->name;
    }

    /**
     * The compare method only compares value.
     */
    public function compare(Bind $b)
    {
        return $this->value === $b->value;
    }

    public static function bindArray(array $array)
    {
        $args = array();
        foreach ($array as $key => $value) {
            $args[$key] = new self($key, $value);
        }

        return $args;
    }
}
