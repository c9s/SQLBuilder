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
use LogicException;

class AlterTableModifyColumn implements ToSqlInterface
{
    protected $column;

    protected $after;

    protected $first;

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

    public function first()
    {
        $this->first = true;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = '';
        if ($driver instanceof MySQLDriver) {
            $sql = 'MODIFY COLUMN ';
            if (!$this->column->getType()) {
                throw new IncompleteSettingsException('Missing column type');
            }
            $sql .= $this->column->buildDefinitionSql($driver, $args);

            if ($this->after) {
                $sql .= ' AFTER ' . $driver->quoteIdentifier($this->after);
            } else if ($this->first) {
                $sql .= ' FIRST';
            }

        } elseif ($driver instanceof PgSQLDriver) {

            // ALTER TABLE distributors RENAME CONSTRAINT zipchk TO zip_check;
            $sql = 'ALTER COLUMN ';
            $sql .= $driver->quoteIdentifier($this->column->getName());

            if ($type = $this->column->getType()) {
                $sql .= ' TYPE ' . $type;
            } elseif ($default = $this->column->default) {
                $sql .= ' SET DEFAULT ' . $driver->deflate($default);
            } elseif ($this->column->nullDefined()) {
                if ($this->column->null === true) {
                    $sql .= ' DROP NOT NULL';
                } elseif($this->column->null === false) {
                    $sql .= ' SET NOT NULL';
                }
            } else {
                throw new IncompleteSettingsException('Empty column attribute ');
            }

        } else {
            throw new UnsupportedDriverException;
        }
        return $sql;
    }
}




