<?php
namespace SQLBuilder;

class Bind { 

    public $name;

    public $variable;

    public function __construct($name, $value = NULL)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue($value)
    {
        return $this->value;
    }

    public function getName() {
        return $this->name;
    }

    public function getMark() {
        return ':' . $this->name;
    }
}

