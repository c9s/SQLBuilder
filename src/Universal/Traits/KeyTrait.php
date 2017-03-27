<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Universal\Syntax\ColumnNames;
use SQLBuilder\Universal\Syntax\KeyReference;

/**
 * Class KeyTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait KeyTrait
{
    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var ColumnNames
     */
    protected $keyColumns;

    protected $indexName;

    protected $indexType;

    /**
     * @var KeyReference
     */
    protected $references;

    public function primaryKey($columns)
    {
        $this->keyType    = 'PRIMARY KEY';
        $this->keyColumns = new ColumnNames($columns);

        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name,...)
     *    reference_definition.
     *
     * @param $keyColumns
     *
     * @return $this
     */
    public function foreignKey($keyColumns)
    {
        $this->keyType    = 'FOREIGN KEY';
        $this->keyColumns = new ColumnNames($keyColumns);

        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type]
     *    (index_col_name,...) [index_type].
     *
     * @param $columns
     *
     * @return $this
     */
    public function uniqueKey($columns)
    {
        $this->keyType    = 'UNIQUE KEY';
        $this->keyColumns = new ColumnNames($columns);

        return $this;
    }

    /**
     * @param $columns
     *
     * @return $this
     */
    public function index($columns)
    {
        $this->keyType    = 'INDEX';
        $this->keyColumns = new ColumnNames($columns);

        return $this;
    }

    /**
     * @param $indexName
     *
     * @return $this
     */
    public function name($indexName)
    {
        $this->indexName = $indexName;

        return $this;
    }

    /**
     * For MySQL is:
     *
     *      USING {BTREE | HASH}
     *
     * Which is not different from the `using` clause of PostgreSQL:
     *
     *      USING INDEX TABLESPACE tablespace
     *
     * @param string $indexType
     *
     * @return $this
     */
    public function using($indexType)
    {
        $this->indexType = $indexType;

        return $this;
    }

    /**
     * @param string     $tableName
     * @param null|array $columns
     *
     * @return \SQLBuilder\Universal\Syntax\KeyReference
     */
    public function references($tableName, $columns = null)
    {
        if ($columns && !is_array($columns)) {
            $columns = [$columns];
        }

        return $this->references = new KeyReference($tableName, $columns);
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildKeyClause(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = $this->keyType;

        // MySQL supports custom index name and index type
        if ($driver instanceof MySQLDriver) {
            if ($this->indexName) {
                $sql .= ' ' . $driver->quoteIdentifier($this->indexName);
            }
            if ($this->indexType) {
                $sql .= ' USING ' . $this->indexType;
            }
        }
        $sql .= ' (' . $this->keyColumns->toSql($driver, $args) . ')';
        if ($this->references) {
            $sql .= ' ' . $this->references->toSql($driver, $args);
        }

        return $sql;
    }
}
