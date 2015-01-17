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

class AlterTableRenameColumn implements ToSqlInterface
{
    protected $fromColumn;

    protected $toColumn;

    public function __construct($fromColumn, Column $toColumn) {
        $this->fromColumn = $fromColumn;
        $this->toColumn = $toColumn;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = '';
        if ($driver instanceof MySQLDriver) {

            $sql = 'CHANGE COLUMN ';
            if (is_string($this->fromColumn)) {
                $sql .= $driver->quoteIdentifier($this->fromColumn);
            } elseif ($this->fromColumn instanceof Column) {
                $sql .= $driver->quoteIdentifier($this->fromColumn->getName());
            }

            // the 'toColumn' must be a type of Column, we need at least column type to rename.
            $sql .= ' ' . $driver->quoteIdentifier($this->toColumn->getName()) . ' ' . $this->toColumn->getType();

        } elseif ($driver instanceof PgSQLDriver) {

            // ALTER TABLE distributors RENAME CONSTRAINT zipchk TO zip_check;
            $sql = 'RENAME COLUMN ';
            if (is_string($this->fromColumn)) {
                $sql .= $driver->quoteIdentifier($this->fromColumn);
            } elseif ($this->fromColumn instanceof Column) {
                $sql .= $driver->quoteIdentifier($this->fromColumn->getName());
            }

            // the 'toColumn' must be a type of Column, we need at least column type to rename.
            $sql .= ' TO ' . $driver->quoteIdentifier($this->toColumn->getName());

        } else {
            throw new UnsupportedDriverException;
        }
        return $sql;
    }
}




