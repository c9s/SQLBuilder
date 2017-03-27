<?php

namespace SQLBuilder;

/**
 * Class Raw
 *
 * @package SQLBuilder
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class Raw
{
    public $value;

    /**
     * Raw constructor.
     *
     * @param $rawValue
     */
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @param \SQLBuilder\Raw $b
     *
     * @return int
     */
    public function compare(Raw $b)
    {
        if ($this->value === $b->value) {
            return 0;
        }

        return strcmp($this->value, $b->value);
    }

    /**
     * @param $array
     *
     * @return \SQLBuilder\Raw
     */
    public static function __set_state($array)
    {
        return new self($array['value']);
    }
}
