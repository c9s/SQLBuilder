<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\ColumnNames;
use SQLBuilder\Universal\Syntax\Constraint;
use SQLBuilder\Universal\Syntax\KeyReference;
use SQLBuilder\Universal\Traits\KeyTrait;

class Constraint implements ToSqlInterface
{
    use KeyTrait;

    protected $symbol;

    public function __construct($symbol = NULL)
    {
        $this->symbol = $symbol;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'CONSTRAINT';
        
        // constrain symbol is optional but only supported by MySQL
        if ($this->symbol) {
            $sql .= ' ' . $driver->quoteIdentifier($this->symbol);
        }
        $sql .= ' ' . $this->buildKeyClause($driver, $args);
        return $sql;
    }
}



