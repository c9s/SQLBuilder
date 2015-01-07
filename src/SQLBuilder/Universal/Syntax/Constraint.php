<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\ColumnNames;

class Constraint implements ToSqlInterface
{
    protected $name;

    protected $type;

    protected $columns;

    protected $indexName;

    protected $indexType;

    protected $references;


    public function __construct($name = NULL)
    {
        $this->name = $name;
    }

    public function primaryKey($columns) 
    {
        $this->type = 'PRIMARY KEY';
        $this->columns = new ColumnNames($columns);
        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name,...) 
     *    reference_definition
     */
    public function foreignKey($columns) 
    {
        $this->type = 'FOREIGN KEY';
        $this->columns = new ColumnNames($columns);
        return $this;
    }

    /**
     * [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] 
     *    (index_col_name,...) [index_type]
     */
    public function unique($columns)
    {
        $this->type = 'UNIQUE';
        $this->columns = new ColumnNames($columns);
        return $this;
    }

    public function index($indexName)
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
        return $this->references = new ConstraintReference($tableName, $columns);
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'CONSTRAINT';
        
        // constrain symbol is optional but only supported by MySQL
        if ($driver instanceof MySQLDriver && $this->name) {
            $sql .= ' ' . $driver->quoteIdentifier($this->name);
        }

        $sql .= ' ' . $this->type;

        // MySQL supports custom index name and index type
        if ($driver instanceof MySQLDriver) {
            if ($this->indexName) {
                $sql .= ' ' . $driver->quoteIdentifier($this->indexName);
            }
            if ($this->indexType) {
                $sql .= ' ' . $this->indexType;
            }
        }

        $sql .= ' (' . $this->columns->toSql($driver, $args) . ')';

        if ($this->references) {
            $sql .= ' ' . $this->references->toSql($driver, $args);
        }
        return $sql;
    }
}



