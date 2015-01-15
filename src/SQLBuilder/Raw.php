<?php
namespace SQLBuilder;

class Raw
{
    public $value;

    public function __construct($rawValue)
    {
        $this->value = $rawValue;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRaw() {
        return $this->value;
    }

    public function __toString() {
        return $this->value;
    }
}



