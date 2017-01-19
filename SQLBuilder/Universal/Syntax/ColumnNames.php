<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class ColumnNames
{
    protected $columns = array();

    public function __construct($columns)
    {
        // Convert string to array(string)
        $this->columns = (array) $columns;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        foreach ($this->columns as $col) {
            $sql .= $driver->quoteIdentifier($col).',';
        }

        return rtrim($sql, ',');
    }
}
