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
        $sql = 'alter table ' . $this->driver->getQuoteTableName( $table )
             . ' add column ' . $this->driver->getQuoteColumn( $column->name );

        // build attributes
        if( isset($column->type) ) {
            $sql .= ' ' . $column->type;
        }

        if( isset($column->default) ) {
            $sql .= ' default ' . $column->default;
        }

        if( $column->isNull ) {
            $sql .= ' is null';
        }
        elseif( $column->isNotNull ) {
            $sql .= ' is not null';
        }
    }
}

