<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Driver;
use SQLBuilder\Inflator;

class MySQLDriver extends Driver
{

    public $quoteColumn = false;
    public $quoteTable = false;

    public function __construct()
    {
        // we keep this for backward compatibiltiy
        $this->type = 'mysql';

        // default escaper (we can override this by giving 
        // new callback)
        $this->escaper = 'addslashes';
        $this->inflator = new Inflator($this);
    }

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


