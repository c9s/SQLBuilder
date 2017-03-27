<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Traits\ConstraintTrait;

/**
 * Class CreateTableQuery
 *
 * MySQL Create Table Syntax.
 *
 * @see     http://dev.mysql.com/doc/refman/5.0/en/create-table.html * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class CreateTableQuery implements ToSqlInterface
{
    use ConstraintTrait;

    /**
     * @var string
     */
    protected $tableName;

    protected $engine;

    protected $temporary;

    protected $columns = [];

    /**
     * CreateTableQuery constructor.
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @param string $tableName
     *
     * @return $this
     */
    public function table($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param $engine
     *
     * @return $this
     */
    public function engine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @param $name
     *
     * @return \SQLBuilder\Universal\Syntax\Column
     */
    public function column($name)
    {
        $col             = new Column($name);
        $this->columns[] = $col;

        return $col;
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
        $sql = 'CREATE';
        if ($this->temporary) {
            $sql .= ' TEMPORARY';
        }
        $sql .= ' TABLE ' . $driver->quoteIdentifier($this->tableName);
        $sql .= '(';
        foreach ($this->columns as $col) {
            $sql .= "\n" . $col->toSql($driver, $args) . ',';
        }

        if ($constraints = $this->getConstraints()) {
            foreach ($constraints as $constraint) {
                $sql .= "\n" . $constraint->toSql($driver, $args) . ',';
            }
        }

        $sql = rtrim($sql, ',') . "\n)";

        if ($this->engine && $driver instanceof MySQLDriver) {
            $sql .= ' ENGINE=' . $this->engine;
        }

        return $sql;
    }
}
