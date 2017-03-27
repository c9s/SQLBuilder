<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

class AlterTableAdd implements ToSqlInterface
{
    /**
     * @var ToSqlInterface|string
     */
    protected $subQuery;

    /**
     * AlterTableAdd constructor.
     *
     * @param $anything
     */
    public function __construct($anything)
    {
        $this->subQuery = $anything;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->subQuery instanceof ToSqlInterface) {
            return 'ADD ' . $this->subQuery->toSql($driver, $args);
        }

        return 'ADD ' . $this->subQuery;
    }
}
