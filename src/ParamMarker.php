<?php

namespace SQLBuilder;

/**
 * Class ParamMarker
 *
 * ParamMarker is a data type for parameter mark without binding
 * value.
 *
 * Used for question mark and named mark
 *
 * @package SQLBuilder
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class ParamMarker
{
    public $value;

    /**
     * ParamMarker constructor.
     *
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getMarker()
    {
        return '?';
    }
}
