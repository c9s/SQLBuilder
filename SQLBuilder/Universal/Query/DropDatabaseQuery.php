<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Traits\IfExistsTrait;

class DropDatabaseQuery implements ToSqlInterface
{
    use IfExistsTrait;

    protected $dbName;

    public function __construct($name = null)
    {
        $this->dbName = $name;
    }

    public function drop($name)
    {
        $this->dbName = $name;

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'DROP DATABASE';
        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildIfExistsClause();
        }
        $sql .= ' '.$driver->quoteIdentifier($this->dbName);

        return $sql;
    }
}
