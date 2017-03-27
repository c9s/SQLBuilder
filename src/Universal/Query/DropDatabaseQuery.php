<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\IfExistsTrait;

/**
 * Class DropDatabaseQuery
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class DropDatabaseQuery implements ToSqlInterface
{
    use IfExistsTrait;

    protected $dbName;

    /**
     * DropDatabaseQuery constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->dbName = $name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function drop($name)
    {
        $this->dbName = $name;

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
        $sql = 'DROP DATABASE';
        if ($driver instanceof MySQLDriver) {
            $sql .= $this->buildIfExistsClause();
        }
        $sql .= ' ' . $driver->quoteIdentifier($this->dbName);

        return $sql;
    }
}
