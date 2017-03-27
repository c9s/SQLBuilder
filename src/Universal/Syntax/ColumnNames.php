<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

/**
 * Class ColumnNames
 *
 * @package SQLBuilder\Universal\Syntax
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class ColumnNames
{
    /**
     * @var array
     */
    protected $columns = [];

    /**
     * ColumnNames constructor.
     *
     * @param string|array $columns
     */
    public function __construct($columns)
    {
        // Convert string to array(string)
        $this->columns = (array)$columns;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        foreach ($this->columns as $col) {
            $sql .= $driver->quoteIdentifier($col) . ',';
        }

        return rtrim($sql, ',');
    }
}
