<?php
namespace SQLBuilder;
use SQLBuilder\Column;
use RuntimeException;

class MigrationBuilder
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
        $sql = ' ADD COLUMN ' . $this->driver->getQuoteColumn( $column->name );

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
            $sql .= ' DEFAULT ' . $this->driver->inflate($default);
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
        $sql  = 'ALTER TABLE ' . $this->driver->getQuoteTableName( $table );
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
        $sql  = 'ALTER TABLE ' . $this->driver->getQuoteTableName( $table );
        return $sql . $this->addColumnClause($column);
    }

    public function dropTable($table)
    {
        return 'DROP TABLE ' . $this->driver->getQuoteTableName($table);
    }

    public function dropColumn($table,$columnName)
    {
        $sql = 'ALTER TABLE ' . $this->driver->getQuoteTableName($table)
               . ' DROP COLUMN ' . $this->driver->getQuoteColumn($columnName);
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
        $self = $this;
        $sql = 'CREATE INDEX ' . $this->driver->getQuoteTableName($indexName) 
            . ' ON ' . $this->driver->getQuoteTableName($table);
        if( is_array($columnNames) ) {
            $sql .= ' (' . join(',' , array_map( function($name) use ($self) { 
                                        return $self->driver->getQuoteColumn( $name );
                                    }, $columnNames ) )
                . ')';
        }
        else {
            $sql .= ' (' . $this->driver->getQuoteColumn( $columnNames ) . ')';
        }
        return $sql;
    }


    /**
     * Add reference

     PostgreSQL version:

        ALTER TABLE products ADD FOREIGN KEY (product_group_id) REFERENCES product_groups;
        ALTER TABLE employee ADD FOREIGN KEY (group_id) REFERENCES product_groups;
        ALTER TABLE items add foreign key (vendor_id) references vendors(vendor_code);

     Works for PostgreSQL and MySQL

        ALTER TABLE items ADD COLUMN vendor_id integer REFERENCES vendors(vendor_code);

        http://www.postgresql.org/docs/8.1/static/ddl-alter.html

    SQL-92 syntax

        ALTER TABLE child ADD CONSTRAINT fk_child_parent
                    FOREIGN KEY (parent_id) 
                    REFERENCES child(id);

    SQLite ??? (is not supported)

    Usage:

        $migration->addForeignKey('product_id','products');
        $migration->addForeignKey('product_id','products','id');


     */
    public function addForeignKey($table,$columnName,$referenceTable,$referenceColumn = null) 
    {
        // SQLite doesn't support ADD CONSTRAINT
        if( 'sqlite' === $this->driver->type ) {
            return;
        }

        // ALTER TABLE employee ADD FOREIGN KEY (group_id) REFERENCES product_groups;
        $sql = 'ALTER TABLE ' 
            . $this->driver->getQuoteTableName($table)
            . ' ADD FOREIGN KEY '
            . '(' . $this->driver->getQuoteTableName($columnName) . ')'
            . ' REFERENCES '
            . $this->driver->getQuoteTableName($table)
            . ( $referenceColumn ? '(' . $this->driver->getQuoteColumn($referenceColumn) . ')' : '' )
            ;
        return $sql;
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
            $sql = 'ALTER TABLE ' . $this->driver->getQuoteTableName($table)
                . ' RENAME COLUMN '
                . $this->driver->getQuoteColumn( $columnName )
                . ' TO '
                . $this->driver->getQuoteColumn( $newColumnName );
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
        $sql = '';
        switch( $this->driver->type )
        {
            case 'mysql':
                $sql = 'DROP INDEX ' 
                    . $this->driver->getQuoteTableName($indexName) 
                    . ' ON ' . $this->driver->getQuoteTableName($table);
            break;
            case 'sqlite':
            case 'pgsql':
                $sql = 'DROP INDEX ' . $this->driver->getQuoteTableName($indexName);
            break;
        }
        return $sql;
    }

}

