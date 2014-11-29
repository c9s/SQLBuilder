<?php
namespace SQLBuilder\Driver;

class MySQLDriver extends BaseDriver
{

    public $quoteColumn = false;
    public $quoteTable = false;


    /*
    public function quote($value) { return $value; }

    public function deflate($value) { return $value; }
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
            return '`' . $name . '`';
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
            return '`' . $name . '`';
        }
        return $name;
    }
}


