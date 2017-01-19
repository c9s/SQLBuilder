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
    public function getRaw()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function compare(Raw $b)
    {
        if ($this->value === $b->value) {
            return 0;
        } else {
            return strcmp($this->value, $b->value);
        }
    }

    public static function __set_state($array)
    {
        return new self($array['value']);
    }
}
