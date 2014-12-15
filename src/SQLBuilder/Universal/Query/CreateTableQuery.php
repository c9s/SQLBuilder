<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Column;

class CreateTableQuery implements ToSqlInterface
{

    protected $tableName;

    protected $columns = array();

    public function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function table($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function column($name) {
        $col = new Column($name);
        $this->columns[] = $col;
        return $col;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'CREATE TABLE ' . $driver->quoteIdentifier($this->tableName);
        $sql .= '(';
        $columnClauses = array();
        foreach($this->columns as $col) {
            $columnClauses[] = $col->toSql($driver, $args);
        }
        $sql .= join(",", $columnClauses);
        $sql .= ')';
        return '';
    }
}




