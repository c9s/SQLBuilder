<?php

namespace SQLBuilder;

/**
 * Class Bind
 *
 * @package SQLBuilder
 *
 * @property string marker
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class Bind
{
    /**
     * @var string
     */
    protected $name;

    protected $value;

    /**
     * Bind constructor.
     *
     * @param string $name
     * @param null   $value
     */
    public function __construct($name, $value = null)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMarker()
    {
        return ':' . $this->name;
    }

    /**
     * The compare method only compares value.
     *
     * @param \SQLBuilder\Bind $b
     *
     * @return bool
     */
    public function compare(Bind $b)
    {
        return $this->value === $b->value;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public static function bindArray(array $array)
    {
        $args = [];
        foreach ($array as $key => $value) {
            $args[$key] = new self($key, $value);
        }

        return $args;
    }
}
