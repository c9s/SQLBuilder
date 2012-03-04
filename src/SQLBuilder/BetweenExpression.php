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
        return $this->driver->getQuoteColumn($this->column) . ' BETWEEN ' 
            . $this->driver->inflate( $this->from )
            .  ' AND ' 
            . $this->driver->inflate( $this->to );
    }

}

