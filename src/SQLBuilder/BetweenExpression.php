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

    public function toSql()
    {
        return $this->driver->getQuoteColumn($this->column) . ' BETWEEN ' 
            . $this->driver->escape( $this->from )
            .  ' AND ' 
            . $this->driver->escape( $this->to );
    }

}

