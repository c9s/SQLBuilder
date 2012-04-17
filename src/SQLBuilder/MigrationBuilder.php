<?php
namespace SQLBuilder;

class MigrationBuilder
{
    public $driver;


    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function addColumn( $table, $column , $attributes = array() ) 
    {
        $sql = 'alter table ' . $this->driver->getQuoteTableName( $table )
             . ' add column ' . $this->driver->getQuoteColumn( $column );

        // build attributes
        if( isset($attributes['type']) ) {
            $sql .= ' ' . $attributes['type'];
        }

        if( isset($attributes['default']) ) {
            $sql .= ' default ' . $attributes['default'];
        }

        if( isset($attributes['is_null'] && $attributes['is_null'] ) ) {
            $sql .= ' is null';
        }
        else {
            $sql .= ' is not null';

        }

    }


}

