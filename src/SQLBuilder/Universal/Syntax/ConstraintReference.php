<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;

/**

MySQL Constraint Syntax
 
  [CONSTRAINT [symbol]] FOREIGN KEY
    [index_name] (index_col_name, ...)
    REFERENCES tbl_name (index_col_name,...)
    [ON DELETE reference_option]
    [ON UPDATE reference_option]
    reference_option:
        RESTRICT | CASCADE | SET NULL | NO ACTION

  @see http://dev.mysql.com/doc/refman/5.0/en/create-table-foreign-keys.html
 */
class ConstraintReference implements ToSqlInterface
{
    protected $tableName;

    protected $columns;

    protected $onDeleteAction;

    protected $onUpdateAction;

    public function __construct($tableName, array $columns = NULL)
    {
        $this->tableName = $tableName;
        if ($columns) {
            $this->columns = $columns;
        }
    }

    public function columns(array $columns) {
        $this->columns = $columns;
        return $this;
    }

    /**
     * RESTRICT | CASCADE | SET NULL | NO ACTION
     */
    public function onDelete($action) {
        $this->onDeleteAction = $action;
        return $this;
    }

    /**
     * RESTRICT | CASCADE | SET NULL | NO ACTION
     */
    public function onUpdate($action) {
        $this->onUpdateAction = $action;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'REFERENCES ' . $driver->quoteIdentifier($this->tableName);

        $sql .= ' (';
        foreach($this->columns as $col) {
            $sql .= $driver->quoteIdentifier($col) . ',';
        }
        $sql = rtrim($sql,',') . ')';

        if ($this->onUpdateAction) {
            $sql .= ' ON UPDATE ' . $this->onUpdateAction;
        }
        if ($this->onDeleteAction) {
            $sql .= ' ON DELETE ' . $this->onDeleteAction;
        }

        return $sql;
    }

}
