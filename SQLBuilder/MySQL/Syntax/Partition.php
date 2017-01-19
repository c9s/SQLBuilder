<?php

namespace SQLBuilder\MySQL\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

/**
 * Partition is only supported for MySQL.
 *
 * @see http://dev.mysql.com/doc/refman/5.7/en/partitioning-selection.html
 */
class Partition
{
    public $names = array();

    public function __construct(array $names)
    {
        $this->names = $names;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return ' PARTITION ('.implode(',', $this->names).')';
    }
}
