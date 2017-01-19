<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

/**
 @see http://dev.mysql.com/doc/refman/5.0/en/create-table-foreign-keys.html
 */
class KeyReference implements ToSqlInterface
{
    protected $tableName;

    protected $columns;

    protected $onDeleteAction;

    protected $onUpdateAction;

    public function __construct($tableName, array $columns = null)
    {
        $this->tableName = $tableName;
        if ($columns) {
            $this->columns = new ColumnNames($columns);
        }
    }

    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * RESTRICT | CASCADE | SET NULL | NO ACTION.
     */
    public function onDelete($action)
    {
        $this->onDeleteAction = $action;

        return $this;
    }

    /**
     * RESTRICT | CASCADE | SET NULL | NO ACTION.
     */
    public function onUpdate($action)
    {
        $this->onUpdateAction = $action;

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'REFERENCES '.$driver->quoteIdentifier($this->tableName);

        $sql .= ' ('.$this->columns->toSql($driver, $args).')';

        if ($this->onUpdateAction) {
            $sql .= ' ON UPDATE '.$this->onUpdateAction;
        }
        if ($this->onDeleteAction) {
            $sql .= ' ON DELETE '.$this->onDeleteAction;
        }

        return $sql;
    }
}
