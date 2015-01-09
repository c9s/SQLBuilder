<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Traits\KeyTrait;
use SQLBuilder\ArgumentArray;

class AlterTableAddConstraint implements ToSqlInterface
{
    use KeyTrait;

    protected $action;

    protected $constraint;

    public function constraint($symbol)
    {
        return $this->constraint = new Constraint($symbol, $this);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        if ($this->constraint) {
            return 'ADD ' . $this->constraint->toSql($driver, $args);
        }
        return 'ADD ' . $this->buildKeyClause($driver, $args);
    }
}




