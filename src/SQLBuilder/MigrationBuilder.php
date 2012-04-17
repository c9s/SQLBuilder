<?php
namespace SQLBuilder;

class Column
{
    public $name;

    public $attributes = array();

    static function create($name)
    {
        return new self($name);
    }

    function __construct($name)
    {
        $this->name = $name;
        return $this;
    }

    function __set($n,$v)
    {
        $this->attributes[ $n ] = $v;
    }

    function __isset($n)
    {
        return isset( $this->attributes[ $n ] );
    }

    function __get($n)
    {
        if( isset( $this->attributes[ $n ] ) )
            return $this->attributes[ $n ];
    }

    function __call($m,$a)
    {
        if( empty($a) ) {
            $this->attributes[ $m ] = true;
        }
        elseif( count($a) > 1 ) {
            $this->attributes[ $m ] = $a;
        }
        else {
            $this->attributes[ $m ] = $a[0];
        }
        return $this;
    }

    function timestamp()
    {
        $this->type = 'timestamp';
        return $this;
    }

    function varchar($length)
    {
        $this->type = "varchar($length)";
        return $this;
    }

    function blob()
    {
        $this->type = 'blob';
        return $this;
    }

    function integer()
    {
        $this->type = $integer;
        return $this;
    }


    function isNull()
    {
        $this->isNotNull = true;
        return $this;
    }

    function isNotNull()
    {
        $this->notNull = true;
        return $this;
    }

}

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

