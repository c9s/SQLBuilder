<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\ColumnNames;
use SQLBuilder\Universal\Syntax\Constraint;
use SQLBuilder\Universal\Syntax\KeyReference;

trait KeyTrait {

    protected $keyName;

    protected $keyColumns;

    protected $indexName;

    protected $indexType;

    protected $references;

    public function primaryKey($columns) 
    {
        $this->keyType = 'PRIMARY KEY';
        $this->keyColumns = new ColumnNames($columns);
        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name,...) 
     *    reference_definition
     */
    public function foreignKey($keyColumns) 
    {
        $this->keyType = 'FOREIGN KEY';
        $this->keyColumns = new ColumnNames($keyColumns);
        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] 
     *    (index_col_name,...) [index_type]
     */
    public function uniqueKey($columns)
    {
        $this->keyType = 'UNIQUE KEY';
        $this->keyColumns = new ColumnNames($columns);
        return $this;
    }

    public function index($columns)
    {
        $this->keyType = 'INDEX';
        $this->keyColumns = new ColumnNames($columns);
        return $this;
    }

    public function name($indexName)
    {
        $this->indexName = $indexName;
        return $this;
    }


    /**
     * @param string $indexType 
     *
     * For MySQL is:
     *
     *      USING {BTREE | HASH}
     *
     * Which is not different from the `using` clause of PostgreSQL:
     *
     *      USING INDEX TABLESPACE tablespace
     *
     */
    public function using($indexType)
    {
        $this->indexType = $indexType;
        return $this;
    }

    public function references($tableName, $columns = NULL) {
        if ($columns && !is_array($columns)) {
            $columns = array($columns);
        }
        return $this->references = new KeyReference($tableName, $columns);
    }


    public function buildKeyClause(BaseDriver $driver, ArgumentArray $args) {
        $sql = $this->keyType;

        // MySQL supports custom index name and index type
        if ($driver instanceof MySQLDriver) {
            if ($this->indexName) {
                $sql .= ' ' . $driver->quoteIdentifier($this->indexName);
            }
            if ($this->indexType) {
                $sql .= ' ' . $this->indexType;
            }
        }
        $sql .= ' (' . $this->keyColumns->toSql($driver, $args) . ')';
        if ($this->references) {
            $sql .= ' ' . $this->references->toSql($driver, $args);
        }
        return $sql;
    }

}


