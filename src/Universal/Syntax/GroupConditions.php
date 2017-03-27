<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

class GroupConditions extends Conditions
{
    public $parent;

    /**
     * GroupConditions constructor.
     *
     * @param array $parent
     */
    public function __construct($parent)
    {
        parent::__construct();

        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function endgroup()
    {
        return $this->parent;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return '(' . parent::toSql($driver, $args) . ')';
    }
}
