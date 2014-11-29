<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Inflator;

class SQLiteDriver extends BaseDriver
{

    public function quoteColumn($name)
    {
        if ($this->quoteColumn) {
            // return raw value if column name contains (non-word chars), eg: min( ), max( )
            if ( preg_match('/\W/',$name) ) {
                return $name;
            }
            return '`' . $name . '`';
        }
        return $name;
    }

    public function quoteTableName($name)
    {
        return $name;
    }

}

