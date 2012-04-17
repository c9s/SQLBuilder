<?php
namespace SQLBuilder;
use SQLBuilder\Column;

class MigrationBuilder
{
    public $driver;


    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function addColumn( $table, $column ) 
    {
        $sql = 'ALTER TABLE ' . $this->driver->getQuoteTableName( $table )
             . ' ADD COLUMN ' . $this->driver->getQuoteColumn( $column->name );

        // build attributes
        if( isset($column->type) ) {
            $sql .= ' ' . $column->type;
        }

        if( $column->primary ) {
            $sql .= ' PRIMARY KEY';
        }

        if( isset($column->default) ) {
            $sql .= ' DEFAULT ' . $column->default;
        }

        if( $column->unique ) {
            $sql .= ' UNIQUE';
        }
        
        if( $column->isNull ) {
            $sql .= ' IS NULL';
        }
        elseif( $column->notNull ) {
            $sql .= ' NOT NULL';
        }
        return $sql;
    }

    public function dropColumn($table,$columnName)
    {
        $sql = 'ALTER TABLE ' . $this->driver->getQuoteTableName($table)
               . ' DROP COLUMN ' . $this->driver->getQuoteColumn( $columnName );
        return $sql;
    }

    public function createIndex($table,$indexName,$columnNames)
    {
        $self = $this;
        $sql = "CREATE INDEX " . $this->driver->getQuoteTableName($indexName) . " ON " . $this->driver->getQuoteTableName($table);
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

}

