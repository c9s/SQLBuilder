<?php

class DriverFactory 
{
    static function create_sqlite_driver()
    {
        $d = new SQLBuilder\Driver;
        $d->configure('driver','sqlite');
        return $d;
    }

    static function create_pgsql_driver()
    {
        $d = new SQLBuilder\Driver;
        $d->configure('driver','pgsql');
        $d->configure('quote_column',true);
        $d->configure('placeholder','named');
        return $d;
    }

    static function create_mysql_driver()
    {
        $d = new SQLBuilder\Driver;
        $d->configure('driver','mysql');
        $d->configure('quote_column',true);
        $d->configure('placeholder','named');
        return $d;
    }
}

