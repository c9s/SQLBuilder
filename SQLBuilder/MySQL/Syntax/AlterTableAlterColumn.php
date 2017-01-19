<?php

namespace SQLBuilder\MySQL\Syntax;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\Column;
use InvalidArgumentException;

class AlterTableAlterColumn implements ToSqlInterface
{
    protected $name;

    protected $defaultValue;

    protected $clauseType;

    const SET_DEFAULT = 1;
    const DROP_DEFAULT = 2;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setDefault($value)
    {
        $this->clauseType = self::SET_DEFAULT;
        $this->defaultValue = $value;

        return $this;
    }

    public function dropDefault()
    {
        $this->clauseType = self::DROP_DEFAULT;

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->clauseType == self::SET_DEFAULT) {
            return 'ALTER COLUMN '.$driver->quoteIdentifier($this->name).' SET DEFAULT '.$driver->deflate($this->defaultValue);
        } elseif ($this->clauseType == self::DROP_DEFAULT) {
            return 'ALTER COLUMN '.$driver->quoteIdentifier($this->name).' DROP DEFAULT';
        } else {
            throw new InvalidArgumentException('You should call either setDefault nor dropDefault');
        }
    }
}
