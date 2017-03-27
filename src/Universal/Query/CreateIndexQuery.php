<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;
use SQLBuilder\ToSqlInterface;

/**
 * Class CreateIndexQuery
 *
 * SELECT * FROM points.
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class CreateIndexQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;

    protected $type;

    protected $options = [];

    protected $method;

    protected $name;

    protected $tableName;

    protected $columns;

    protected $storageParameters = [];

    /**
     * CreateIndexQuery constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * MySQL, PostgreSQL.
     *
     * @param null $name
     *
     * @return $this
     */
    public function unique($name = null)
    {
        $this->type = 'UNIQUE';
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * FULLTEXT is only supported on MySQL.
     *
     * MySQL only
     *
     * @param null $name
     *
     * @return $this
     */
    public function fulltext($name = null)
    {
        $this->type = 'FULLTEXT';
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * MySQL only.
     *
     * @param null $name
     *
     * @return $this
     */
    public function spatial($name = null)
    {
        $this->type = 'SPATIAL';
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * MySQL: {BTREE | HASH}
     * PostgreSQL:  {btree | hash | gist | spgist | gin}.
     *
     * @param $method
     *
     * @return $this
     */
    public function using($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function create($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param       $tableName
     * @param array $columns
     *
     * @return $this
     */
    public function on($tableName, array $columns = [])
    {
        $this->tableName = $tableName;
        if (!empty($columns)) {
            $this->columns = $columns;
        }

        return $this;
    }

    /**
     * @param $name
     * @param $val
     *
     * @return $this
     */
    public function with($name, $val)
    {
        $this->storageParameters[$name] = $val;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    protected function buildMySQLQuery(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'CREATE';

        if ($this->type) {
            // validate index type
            $sql .= ' ' . $this->type;
        }

        $sql .= ' INDEX';

        $sql .= ' ' . $driver->quoteIdentifier($this->name) . ' ON ' . $driver->quoteIdentifier($this->tableName);

        if (!empty($this->columns)) {
            $sql .= ' (' . implode(',', $this->columns) . ')';
        }
        if ($this->method) {
            $sql .= ' USING ' . $this->method;
        }

        return $sql;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    protected function buildPgSQLQuery(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'CREATE';

        if ($this->type === 'UNIQUE') {
            $sql .= ' UNIQUE';
        } elseif ($this->type && $this->type === 'UNIQUE') {
            throw new CriticalIncompatibleUsageException();
        }

        $sql .= ' INDEX';

        $sql .= $this->buildConcurrentlyClause($driver, $args);

        $sql .= ' ' . $driver->quoteIdentifier($this->name) . ' ON ' . $driver->quoteIdentifier($this->tableName);

        // TODO: validate method 
        if ($this->method) {
            $sql .= ' USING ' . $this->method;
        }
        if (!empty($this->columns)) {
            $sql .= ' (' . implode(',', $this->columns) . ')';
        }

        if (!empty($this->storageParameters)) {
            $sql .= ' WITH ';
            foreach ($this->storageParameters as $name => $val) {
                $sql .= $name . ' = ' . $val . ',';
            }
            $sql = rtrim($sql, ',');
        }

        // TODO: support tablespace and predicate
        return $sql;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if (!$this->tableName) {
            throw new IncompleteSettingsException('CREATE INDEX Query requires tableName');
        }
        if ($driver instanceof PgSQLDriver) {
            return $this->buildPgSQLQuery($driver, $args);
        } elseif ($driver instanceof MySQLDriver) {
            return $this->buildMySQLQuery($driver, $args);
        } else {
            throw new UnsupportedDriverException($driver, $this);
        }
    }
}
