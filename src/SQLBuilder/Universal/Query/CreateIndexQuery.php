<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;

/**
MySQL Create Index Query
========================

CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name
    [index_type]
    ON tbl_name (index_col_name,...)
    [index_type]

index_col_name:
    col_name [(length)] [ASC | DESC]

index_type:
    USING {BTREE | HASH}

Example Queries
----------------------

    CREATE INDEX part_of_name ON customer (name(10));
    CREATE INDEX id_index ON lookup (id) USING BTREE;


PostgreSQL Create Index Query
==============================

CREATE [ UNIQUE ] INDEX [ CONCURRENTLY ] name ON table [ USING method ]
    ( { column | ( expression ) } [ opclass ] [, ...] )
    [ WITH ( storage_parameter = value [, ... ] ) ]
    [ TABLESPACE tablespace ]
    [ WHERE predicate ]


Example Queries
---------------

    CREATE INDEX ON films ((lower(title)));

    CREATE INDEX title_idx_german ON films (title COLLATE "de_DE");

    CREATE INDEX title_idx_nulls_low ON films (title NULLS FIRST);


    CREATE INDEX pointloc
        ON points USING gist (box(location,location));
    SELECT * FROM points
        WHERE box(location,location) && '(0,0),(1,1)'::box;

    CREATE INDEX CONCURRENTLY sales_quantity_index ON sales_table (quantity);

    CREATE INDEX code_idx ON films (code) TABLESPACE indexspace;

    CREATE UNIQUE INDEX title_idx ON films (title) WITH (fillfactor = 70);
    CREATE INDEX gin_idx ON documents_table USING gin (locations) WITH (fastupdate = off);
    
 */

class CreateIndexQuery implements ToSqlInterface
{
    use ConcurrentlyTrait;

    protected $type;

    protected $options = array();

    protected $method;

    protected $name;

    protected $tableName;

    protected $columns;

    protected $storageParameters = array();

    public function __construct($name = NULL) {
        $this->name = $name;
    }

    /**
     * MySQL, PostgreSQL
     */
    public function unique($name = NULL) {
        $this->type = 'UNIQUE';
        if ($name) {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * FULLTEXT is only supported on MySQL
     *
     * MySQL only
     */
    public function fulltext($name = NULL) {
        $this->type = 'FULLTEXT';
        if ($name) {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * MySQL only
     */
    public function spatial($name = NULL) {
        $this->type = 'SPATIAL';
        if ($name) {
            $this->name = $name;
        }
        return $this;
    }


    /**
     * MySQL: {BTREE | HASH}
     * PostgreSQL:  {btree | hash | gist | spgist | gin}
     */
    public function using($method) 
    {
        $this->method = $method;
        return $this;
    }

    public function create($name) 
    {
        $this->name = $name;
        return $this;
    }

    public function on($tableName, array $columns = array())
    {
        $this->tableName = $tableName;
        if (!empty($columns)) {
            $this->columns = $columns;
        }
        return $this;
    }

    public function with($name, $val) {
        $this->storageParameters[$name] = $val;
        return $this;
    }

    protected function buildMySQLQuery(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'CREATE';

        if ($this->type) {
            // validate index type
            $sql .= ' ' . $this->type;
        }

        $sql .= ' INDEX';

        $sql .= ' ' . $driver->quoteIdentifier($this->name) . ' ON ' . $driver->quoteIdentifier($this->tableName);

        if (!empty($this->columns)) {
            $sql .= ' (' . join(',', $this->columns) . ')';
        }
        if ($this->method) {
            $sql .= ' USING ' . $this->method;
        }
        return $sql;
    }

    protected function buildPgSQLQuery(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'CREATE';

        if ($this->type === 'UNIQUE') {
            $sql .= ' UNIQUE';
        } elseif ($this->type && $this->type === 'UNIQUE') {
            throw new CriticalIncompatibleUsageException;
        }
            
        $sql .= ' INDEX';

        $sql .= $this->buildConcurrentlyClause($driver, $args);

        $sql .= ' ' . $driver->quoteIdentifier($this->name) . ' ON ' . $driver->quoteIdentifier($this->tableName);

        // TODO: validate method 
        if ($this->method) {
            $sql .= ' USING ' . $this->method;
        }
        if (!empty($this->columns)) {
            $sql .= ' (' . join(',', $this->columns) . ')';
        }

        if (!empty($this->storageParameters)) {
            $sql .= ' WITH ';
            foreach($this->storageParameters as $name => $val) {
                $sql .= $name . ' = ' . $val . ',';
            }
            $sql = rtrim($sql, ',');
        }
        // TODO: support tablespace and predicate
        return $sql;
    }


    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        if (!$this->tableName || !$this->name) {
            throw new IncompleteSettingsException('CREATE INDEX Query requires tableName and indexName');
        }
        if ($driver instanceof PgSQLDriver) {
            return $this->buildPgSQLQuery($driver, $args);
        } elseif ($driver instanceof MySQLDriver) {
            return $this->buildMySQLQuery($driver, $args);
        } else {
            throw new UnsupportedDriverException;
        }
    }

}


