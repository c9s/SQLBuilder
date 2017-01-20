<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ArgumentArray;

class AlterTableAddColumn implements ToSqlInterface
{
    protected $column;

    protected $after;

    protected $first;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function after($column)
    {
        if ($column instanceof Column) {
            $this->after = $column->getName();
        } else {
            $this->after = $column;
        }

        return $this;
    }

    public function first()
    {
        $this->first = true;

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'ADD COLUMN ';
        $sql .= $this->column->buildDefinitionSql($driver, $args);

        if ($driver instanceof MySQLDriver) {
            if ($this->after) {
                $sql .= ' AFTER '.$driver->quoteIdentifier($this->after);
            } elseif ($this->first) {
                $sql .= ' FIRST';
            }
        }

        return $sql;
    }
}
