<?php
namespace SQLBuilder;
use SQLBuilder\Column;
use RuntimeException;

/**
 * @codeCoverageIgnore
 *
 * DEPRECATED
 */
class MigrationBuilder 
{
    public $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function _convertArrayToColumn($array) 
    {
        $column = Column::create($array['name']);
        if(isset($array['type']))
            $column->type = $array['type'];
        if(isset($array['null']))
            $column->null();
        if(isset($array['notNull']))
            $column->notNull();
        if(isset($array['unique']))
            $column->unique();
        if(isset($array['default']))
            $column->default($array['default']);
        return $column;
    }

    public function addColumnClause($column) 
    {
        $column = is_array($column) ? $this->_convertArrayToColumn($column) : $column;
        $sql = ' ADD COLUMN ' . $this->driver->quoteColumn( $column->name );

        // build attributes
        if( isset($column->type) ) {
            $sql .= ' ' . $column->type;
        }

        if( $column->primary ) {
            $sql .= ' PRIMARY KEY';
        }

        if( isset($column->default) ) {
            $default = $column->default;

            if( is_callable($default) ) {
                $default = call_user_func($default);
            }
            $sql .= ' DEFAULT ' . $this->driver->deflate($default);
        }

        if( $column->unique ) {
            $sql .= ' UNIQUE';
        }
        if( $column->null ) {
            $sql .= ' NULL';
        }
        elseif( $column->notNull ) {
            $sql .= ' NOT NULL';
        }
        return $sql;
    }


    public function addColumns($table,$columns) 
    {
        $sql  = 'ALTER TABLE ' . $this->driver->quoteTableName( $table );
        $columns = is_object($columns) || !isset($columns[0])
                    ? array($columns)
                    : $columns;
        $clauses = array();
        foreach( $columns as $column ) {
            $clauses[] = $this->addColumnClause($column);
        }
        return $sql . join(',',$clauses);
    }

    public function addColumn($table, $column)
    {
        $sql  = 'ALTER TABLE ' . $this->driver->quoteTableName( $table );
        return $sql . $this->addColumnClause($column);
    }

    public function dropTable($table)
    {
        return 'DROP TABLE ' . $this->driver->quoteTableName($table);
    }

    public function dropColumn($table,$columnName)
    {
        $sql = 'ALTER TABLE ' . $this->driver->quoteTableName($table)
               . ' DROP COLUMN ' . $this->driver->quoteColumn($columnName);
        return $sql;
    }


    /**
     * pgsql create index:
     * @link http://www.postgresql.org/docs/8.2/static/sql-createindex.html
     *
     * mysql:
     * @link http://dev.mysql.com/doc/refman/5.0/en/create-index.html
     */
    public function createIndex($table,$indexName,$columnNames)
    {
        $builder = new IndexBuilder($this->driver);
        return $builder->createIndex($table, $indexName, $columnNames );
    }

    public function addForeignKey($table,$columnName,$referenceTable,$referenceColumn = null) 
    {
        $builder = new IndexBuilder($this->driver);
        return $builder->addForeignKey($table,$columnName,$referenceTable,$referenceColumn);
    }

    public function renameColumn($table,$columnName,$newColumnName)
    {
        $sql = null;
        switch( $this->driver->type ) {
        case 'sqlite':
            throw new RuntimeException("Column renaming is not supported in SQLite.");
            break;
        case 'mysql':
        case 'pgsql':
            $sql = 'ALTER TABLE ' . $this->driver->quoteTableName($table)
                . ' RENAME COLUMN '
                . $this->driver->quoteColumn( $columnName )
                . ' TO '
                . $this->driver->quoteColumn( $newColumnName );
            break;
        }
        return $sql;
    }

    /**
     * mysql
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/drop-index.html
     */
    public function dropIndex($table,$indexName)
    {
        $builder = new IndexBuilder($this->driver);
        return $builder->dropIndex($table,$indexName);
    }

}

