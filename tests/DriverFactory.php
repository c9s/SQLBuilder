<?php
class DriverFactory 
{
    static function create_sqlite_driver()
    {
        $d = new SQLBuilder\Driver\SQLiteDriver;
        return $d;
    }

    static function create_pgsql_driver()
    {
        $d = new SQLBuilder\Driver\PgSQLDriver;
        $d->setQuoteColumn(true);
        $d->setNamedParamMarker();
        return $d;
    }

    static function create_mysql_driver()
    {
        $d = new SQLBuilder\Driver\MySQLDriver;
        $d->setQuoteColumn(true);
        $d->setNamedParamMarker();
        return $d;
    }
}

