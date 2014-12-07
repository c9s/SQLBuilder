<?php
namespace SQLBuilder\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class GroupConditions extends Conditions
{
    public $parent;

    public function __construct($parent) {
        $this->parent = $parent;
    }

    public function endgroup() {
        return $this->parent;
    }


    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return '(' . parent::toSql($driver, $args) . ')';
    }
}



