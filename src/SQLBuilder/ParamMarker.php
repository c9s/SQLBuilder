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
    public $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function getMark() {
        return ':' . $this->name;
    }
}



