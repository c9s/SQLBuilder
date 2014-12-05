<?php
namespace SQLBuilder;

class ParamMarker
{
    public $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function getMark() {
        return ':' . $this->name;
    }
}



