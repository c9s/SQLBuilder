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


/**
 * MySQL Drop table syntax
 *
    DROP [TEMPORARY] TABLE [IF EXISTS]
        tbl_name [, tbl_name] ...
        [RESTRICT | CASCADE]

 */

class DropTableQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;
    use IfExistsTrait;
    use CascadeTrait;
    use RestrictTrait;

    protected $tableNames = array();

    protected $temporary;

    public function __construct($tableNames = NULL) {
        if ($tableNames && is_array($tableNames)) {
            $this->tableNames = $tableNames;
        } elseif (is_string($tableNames)) {
            $this->tableNames = array($tableNames);
        }
    }

    public function table($tableName) {
        $this->tableNames[] = $tableName;
        return $this;
    }

    public function temporary() {
        $this->temporary = true;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'DROP';

        // only for mysql
        if ($driver instanceof MySQLDriver && $this->temporary) {
            $sql .= ' TEMPORARY';
        }

        $sql .= ' TABLE';

        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildConcurrentlyClause();
        }

        $sql .= $this->buildIfExistsClause($driver, $args);

        foreach($this->tableNames as $tableName) {
            $sql .= ' ' . $driver->quoteIdentifier($tableName) . ',';
        }
        $sql = rtrim($sql, ',');


        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildCascadeClause();
            $sql .= $this->buildRestrictClause();
        }
        return $sql;
    }
}

