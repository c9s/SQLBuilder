<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Traits\ConstraintTrait;

/**
 * MySQL Create Table Syntax.
 *
 * @see http://dev.mysql.com/doc/refman/5.0/en/create-table.html
 */
class CreateTableQuery implements ToSqlInterface
{
    use ConstraintTrait;

    protected $tableName;

    protected $engine;

    protected $temporary;

    protected $columns = array();

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function table($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function engine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    public function column($name)
    {
        $col = new Column($name);
        $this->columns[] = $col;

        return $col;
    }

    public function temporary()
    {
        $this->temporary = true;

        return $this;
    }

    /**
     
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'CREATE';
        if ($this->temporary) {
            $sql .= ' TEMPORARY';
        }
        $sql .= ' TABLE '.$driver->quoteIdentifier($this->tableName);
        $sql .= '(';
        $columnClauses = array();
        foreach ($this->columns as $col) {
            $sql .= "\n".$col->toSql($driver, $args).',';
        }

        if ($constraints = $this->getConstraints()) {
            foreach ($constraints as $constraint) {
                $sql .= "\n".$constraint->toSql($driver, $args).',';
            }
        }

        $sql = rtrim($sql, ',')."\n)";

        if ($this->engine && $driver instanceof MySQLDriver) {
            $sql .= ' ENGINE='.$this->engine;
        }

        return $sql;
    }
}
