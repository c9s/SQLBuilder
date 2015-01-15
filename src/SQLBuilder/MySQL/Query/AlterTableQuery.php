<?php
namespace SQLBuilder\MySQL\Query;
use Exception;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Universal\Syntax\AlterTableAddConstraint;
use SQLBuilder\Exception\UnimplementedFunctionException;

class AlterTableQuery implements ToSqlInterface
{
    protected $table;

    protected $specs = array();

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function add()
    {
        $this->specs[] = $spec = new AlterTableAddConstraint;
        return $spec;
    }

    public function changeColumn() {
        throw new UnimplementedFunctionException;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'ALTER TABLE ' . $driver->quoteIdentifier($this->table);
        $alterSpecSqls = array();

        foreach($this->specs as $spec) {
            $alterSpecSqls[] = $spec->toSql($driver, $args);
        }
        $sql .= join(', ', $alterSpecSqls);
        return $sql;
    }


}
