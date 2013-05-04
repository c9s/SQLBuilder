<?php
namespace SQLBuilder;

class IndexBuilder
{
    public $driver;

    public $name;
    public $on;
    public $columns;
    public $concurrently;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function create($name) {
        $this->name = $name;
        return $this;
    }

    public function on($on, $columns = array()) {
        $this->on = $on;
        if ( ! empty($columns) ) {
            $this->columns = $columns;
        }
        return $this;
    }

    public function columns($a) {
        if ( is_string($a) ) {
            $this->columns = func_get_args();
        } elseif ( is_array($a) ) {
            $this->columns = $a;
        }
        return $this;
    }

    public function concurrently() {
        $this->concurrently = true;
        return $this;
    }

    public function build() {
        $self = $this;
        $sql = '';

        $sql .= 'CREATE INDEX ';
        if ($this->concurrently) {
            $sql .= 'CONCURRENTLY ';
        }

        $sql .= $this->driver->getQuoteTableName($this->name);
        $sql .= ' ON ' . $this->driver->getQuoteTableName($this->on);
        $sql .= ' (' 
                . join(',' , array_map( function($name) use ($self) { 
                    return $self->driver->getQuoteColumn( $name );
                }, $this->columns ) ) 
                . ')';
        return $sql;
    }

    /**
     * Create Index
     *
     *
     * CREATE INDEX {index name} ON {table}( {columns...} );
     *
     * pgsql create index:
     * @link http://www.postgresql.org/docs/8.2/static/sql-createindex.html
     *
     *     Concurrently create:
     *
     *     CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
     *
     *     Functional concurrently create:
     *
     *     CREATE INDEX CONCURRENTLY on tokens (substr(token), 0, 8)
     *
     * mysql:
     * @link http://dev.mysql.com/doc/refman/5.0/en/create-index.html
     *
     * @param string $table table nmae
     * @param string $indexName index name
     * @param string[] $columnNames
     */
    public function createIndex($table, $indexName, $columnNames)
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
            return '';
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

