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
use SQLBuilder\Exception\IncompleteSettingsException;

class AlterTableModifyColumn implements ToSqlInterface
{
    protected $column;

    public function __construct(Column $column) {
        $this->column = $column;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = '';
        if ($driver instanceof MySQLDriver) {

            $sql = 'MODIFY COLUMN ';

            /*
            if (is_string($this->column)) {
                $sql .= $driver->quoteIdentifier($this->column);
            } elseif ($this->column instanceof Column) {
                $sql .= $driver->quoteIdentifier($this->column->getName());
            }
            */
            if (!$this->column->getName()) {
                throw new IncompleteSettingsException('Missing column name');
            }
            if (!$this->column->getType()) {
                throw new IncompleteSettingsException('Missing column type');
            }

            $sql .= $this->column->buildSqlMySQL($driver, $args);

        } elseif ($driver instanceof PgSQLDriver) {

            // ALTER TABLE distributors RENAME CONSTRAINT zipchk TO zip_check;
            $sql = 'ALTER COLUMN ';
            if (is_string($this->fromColumn)) {
                $sql .= $driver->quoteIdentifier($this->fromColumn);
            } elseif ($this->fromColumn instanceof Column) {
                $sql .= $driver->quoteIdentifier($this->fromColumn->getName());
            }

            // the 'column' must be a type of Column, we need at least column type to rename.
            $sql .= ' TO ' . $driver->quoteIdentifier($this->column->getName()) . ' ' . $this->column->getType();

        } else {
            throw new UnsupportedDriverException;
        }
        return $sql;
    }
}




