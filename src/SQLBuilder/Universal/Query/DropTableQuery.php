<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;
use SQLBuilder\Universal\Traits\IfExistsTrait;
use SQLBuilder\Universal\Traits\RestrictTrait;
use SQLBuilder\Universal\Traits\CascadeTrait;
use SQLBuilder\Accessor;

class DropTableQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;
    use IfExistsTrait;
    use CascadeTrait;
    use RestrictTrait;

    protected $tableName;

    public function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function table($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'DROP TABLE';

        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildConcurrentlyClause();
        }

        $sql .= $this->buildIfExistsClause($driver, $args);

        $sql .= ' ' . $driver->quoteIdentifier($this->tableName);


        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildCascadeClause();
            $sql .= $this->buildRestrictClause();
        }
        return $sql;
    }
}

