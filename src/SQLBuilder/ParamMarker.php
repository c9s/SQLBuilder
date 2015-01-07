<?php
namespace SQLBuilder;

/**
 * ParamMarker is a data type for parameter mark without binding 
 * value.
 *
 * Used for question mark and named mark
 */
class ParamMarker
{
    public $value;

    public function __construct($value = NULL) {
        $this->value = $value;
    }

    public function getName() {
        return '?';
    }

    public function getMark() {
        return '?';
    }
}



