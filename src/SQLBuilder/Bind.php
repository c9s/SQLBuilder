<?php
namespace SQLBuilder;

class Bind { 

    protected $name;

    protected $value;

    public function __construct($name, $value = NULL)
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

    public function getName() {
        return $this->name;
    }

    public function getMarker() {
        return ':' . $this->name;
    }
}

