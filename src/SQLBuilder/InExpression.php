<?php
namespace SQLBuilder;

class InExpression
{
    public $driver;
    public $column;
    public $values;

    /**
     * Expression class for SQL IN syntax
     *
     *    A IN (1,2,3,4)
     *
     */
    public function __construct($column,$values)
    {
        $this->column = $column;
        $this->values = $values;
    }

    public function toSql()
    {
        $sql = $this->driver->quoteColumn($this->column) . ' IN ' ;
        $escVals = array();
        foreach( $this->values as $val ) {
            $escVals[] = $this->driver->inflate( $val );
        }
        return $sql . '(' . join(', ', $escVals) . ')';
    }

}

