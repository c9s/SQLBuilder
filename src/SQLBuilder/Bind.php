<?php
namespace SQLBuilder;

class Bind { 

    protected $name;

    protected $value;

    public function __construct($name = NULL, $value = NULL)
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

    public function getMark() {
        if ($this->name && is_string($this->name)) {
            return ':' . $this->name;
        }
        return '?';
    }
}

