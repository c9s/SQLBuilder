<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Inflator;

class MySQLDriver extends BaseDriver
{

    public $quoteColumn = false;
    public $quoteTable = false;

    public function __construct()
    {
        // default escaper (we can override this by giving 
        // new callback)
        $this->inflator = new Inflator($this);
    }


    /*
    public function quote($value) { return $value; }

    public function inflate($value) { return $value; }
    */


    /**
     * Check driver optino to quote table name
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     * @return string table name with/without quotes.
     */
    public function quoteTableName($name) 
    {
        if ($this->quoteTable) {
            if (is_string($c)) {
                return $c . $name . $c;
            } else {
                return '`' . $name . '`';
            }
        }
        return $name;
    }

    /**
     * Check driver option to quote column name
     *
     * column quote can be configured by 'quote_column' option.
     *
     * @param string $name column name
     * @return string column name with/without quotes.
     */
    public function quoteColumn($name)
    {
        if ( $c = $this->quoteColumn ) {
            if (preg_match('/\W/',$name)) {
                return $name;
            }
            if (is_string($c)) {
                return $c . $name . $c;
            }
            return '`' . $name . '`';
        }
        return $name;
    }
}


