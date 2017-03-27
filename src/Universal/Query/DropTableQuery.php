<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\CascadeTrait;
use SQLBuilder\Universal\Traits\IfExistsTrait;
use SQLBuilder\Universal\Traits\RestrictTrait;

/**
 * Class DropTableQuery
 *
 * MySQL Drop table syntax.
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class DropTableQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;
    use IfExistsTrait;
    use CascadeTrait;
    use RestrictTrait;

    /**
     * @var array
     */
    protected $tableNames = [];

    protected $temporary;

    /**
     * DropTableQuery constructor.
     *
     * @param null|array|string $tableNames
     */
    public function __construct($tableNames = null)
    {
        if ($tableNames && is_array($tableNames)) {
            $this->tableNames = $tableNames;
        } elseif (is_string($tableNames)) {
            $this->tableNames = [$tableNames];
        }
    }

    /**
     * @param $tableName
     *
     * @return $this
     */
    public function drop($tableName)
    {
        $this->tableNames[] = $tableName;

        return $this;
    }

    /**
     * @return $this
     */
    public function temporary()
    {
        $this->temporary = true;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'DROP';

        // only for mysql
        if ($driver instanceof MySQLDriver && $this->temporary) {
            $sql .= ' TEMPORARY';
        }

        $sql .= ' TABLE';

        if ($driver instanceof PgSQLDriver) {
            $sql .= $this->buildConcurrentlyClause($driver, $args);
        }

        $sql .= $this->buildIfExistsClause($driver, $args);

        foreach ($this->tableNames as $tableName) {
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
