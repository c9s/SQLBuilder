<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Driver;

class MySQLDriver extends Driver
{

    /**
     * Check driver optino to quote table name
     *
     * column quote can be configured by 'quote_table' option.
     *
     * @param string $name table name
     * @return string table name with/without quotes.
     */
    public function getQuoteTableName($name) 
    {
        // Should we quote table name?
        if ( $c = $this->quoteTable ) {
            if( is_string($c) ) {
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
        if ($c = $this->quoteColumn) {
            // return raw value if column name contains (non-word chars), eg: min( ), max( )
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


