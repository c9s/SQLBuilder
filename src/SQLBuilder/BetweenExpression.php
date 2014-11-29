<?php
namespace SQLBuilder;

class BetweenExpression
{
    public $driver;
    public $column;
    public $from;
    public $to;

    public function __construct($column,$from,$to)
    {
        $this->column = $column;
        $this->from = $from;
        $this->to = $to;
    }

    // xxx: process for placeholder
    public function toSql()
    {
        return $this->driver->quoteColumn($this->column) . ' BETWEEN ' 
            . $this->driver->deflate( $this->from )
            .  ' AND ' 
            . $this->driver->deflate( $this->to );
    }

}

