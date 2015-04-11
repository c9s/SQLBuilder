<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Exception\UnsupportedDriverException;

class AlterTableAddColumn implements ToSqlInterface
{
    protected $column;

    protected $after;

    public function __construct(Column $column) {
        $this->column = $column;
    }

    public function after($column) {
        if ($column instanceof Column) {
            $this->after = $column->getName();
        } else {
            $this->after = $column;
        }
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'ADD COLUMN ';
        $sql .= $this->column->buildDefinitionSql($driver, $args);

        if ($driver instanceof MySQLDriver) {
            if ($this->after) {
                $sql .= ' AFTER ' . $driver->quoteIdentifier($this->after);
            }
        }
        return $sql;
    }
}




