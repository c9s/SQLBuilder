<?php
namespace SQLBuilder;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use Exception;

/**
 * @link http://blog.gtuhl.com/2009/08/07/postgresql-tips-and-tricks/
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
 */
class IndexBuilder extends QueryBuilder
{
    public $driver;

    public $unique;
    public $name;
    public $on;
    public $columns;
    public $concurrently;
    public $where;
    public $using;

    public function __construct(BaseDriver $driver)
    {
        $this->driver = $driver;
    }

    public function create($name) {
        $this->name = $name;
        return $this;
    }

    public function unique() {
        $this->unique = true;
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

    public function using($type) {
        $this->using = $type;
        return $this;
    }

    public function build() {
        $self = $this;
        $sql = '';

        $sql .= 'CREATE ';

        if ($this->unique) {
            $sql .= 'UNIQUE ';
        }

        $sql .= 'INDEX ';

        if ($this->concurrently && $this->driver instanceof PgSQLDriver) {
            $sql .= 'CONCURRENTLY ';
        }

        $sql .= $this->driver->quoteTableName($this->name) . ' ';
        $sql .= 'ON ' . $this->driver->quoteTableName($this->on) . ' ';

        if ( $this->using && $this->driver instanceof PgSQLDriver) {
            $sql .= 'USING ' . strtoupper($this->using) . ' ';
        }

        $sql .= '(' 
                . join(',' , array_map( function($n) use ($self) { 
                    if ( is_array($n) ) {
                        if ( count($n) == 2 ) {
                            // column name and appended attributes
                            return $self->driver->quoteColumn( $n[0] ) . ' ' . $n[1];
                        } elseif ( count($n) == 1 ) {
                            // with raw format
                            return $n[0];
                        }
                    } else {
                        return $self->driver->quoteColumn( $n );
                    }
                }, $this->columns ) ) 
                . ')';

        if ( $this->where && $this->where->isComplete() ) {
            $sql .= ' WHERE ' . $this->where->toSql();
        }
        return $sql;
    }

    /**
     * Shorthand method of creating Index
     *
     * CREATE INDEX {index name} ON {table}( {columns...} );
     *
     * @param string $table table nmae
     * @param string $indexName index name
     * @param string[] $columnNames
     */
    public function createIndex($table, $indexName, $columnNames)
    {
        $self = $this;
        $sql = 'CREATE INDEX ' . $this->driver->quoteTableName($indexName) 
            . ' ON ' . $this->driver->quoteTableName($table);
        if( is_array($columnNames) ) {
            $sql .= ' (' . join(',' , array_map( function($name) use ($self) { 
                                        return $self->driver->quoteColumn( $name );
                                    }, $columnNames ) )
                . ')';
        }
        else {
            $sql .= ' (' . $this->driver->quoteColumn( $columnNames ) . ')';
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



    REFERENCES tbl_name (index_col_name,...)
        [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
        [ON DELETE reference_option]
        [ON UPDATE reference_option]

    reference_option:
        RESTRICT | CASCADE | SET NULL | NO ACTION

    SQL-92 syntax

        ALTER TABLE child ADD CONSTRAINT fk_child_parent
                    FOREIGN KEY (parent_id) 
                    REFERENCES child(id)
                    ;

    SQLite ??? (is not supported)

    Usage:

        $migration->addForeignKey('products', 'product_id', 'products');
        $migration->addForeignKey('products', 'product_id', 'products','id');

     */
    public function addForeignKey($table, $columnName, $referenceTable, 
        $referenceColumn, 
        $onDelete = null )
    {
        // SQLite doesn't support ADD CONSTRAINT
        if ($this->driver instanceof SQLiteDriver) {
            return '';
        }

        // ALTER TABLE employee ADD FOREIGN KEY (group_id) REFERENCES product_groups;
        $sql = 'ALTER TABLE ' ;
        $sql .= $this->driver->quoteTableName($table);
        $sql .= ' ADD FOREIGN KEY ';
        $sql .= '(' . $this->driver->quoteTableName($columnName) . ')';
        $sql .= ' REFERENCES ';
        $sql .= $this->driver->quoteTableName($referenceTable);
        $sql .= ( $referenceColumn ? '(' . $this->driver->quoteColumn($referenceColumn) . ')' : '' );

        if ( $onDelete ) {
            // ON DELETE CASCADE
            // ON DELETE RESTRICT
            $sql .= " ON DELETE " . strtoupper($onDelete);
        }
        return $sql;
    }


    /**
     * Build SQL for dropping index.
     *
     * mysql
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/drop-index.html
     */
    public function dropIndex($table, $indexName, $ifExists = true)
    {
        if ($this->driver instanceof MySQLDriver) {
            $sql = 'DROP INDEX ' ;
            if ($ifExists) {
                $sql .= 'IF EXISTS ';
            }
            $sql .= $this->driver->quoteTableName($indexName) 
                . ' ON ' . $this->driver->quoteTableName($table);
            return $sql;

        } elseif ($this->driver instanceof PgSQLDriver) {
            $sql = 'DROP INDEX ';
            if ($ifExists) {
                $sql .= 'IF EXISTS ';
            }
            $sql .= $this->driver->quoteTableName($indexName);
            return $sql;
        } else {
            $sql = 'DROP INDEX ';
            if ($ifExists) {
                $sql .= 'IF EXISTS ';
            }
            $sql .= $this->driver->quoteTableName($indexName);
            return $sql;
        }
    }
}

