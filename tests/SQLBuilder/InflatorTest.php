<?php
use SQLBuilder\Inflator;

class InflatorTest extends PHPUnit_Framework_TestCase
{
    function testBool()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        is( 'TRUE', $driver->deflate( true ) );
        is( 'FALSE', $driver->deflate( false ) );
    }

    function testNumber()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        is( 1 , $driver->deflate( 1 ) );
        is( 1.2 , $driver->deflate( 1.2 ) );
        is( '\'1\'' , $driver->deflate( '1' ) );
        is( 'NULL' , $driver->deflate( null ) );

        $d = new DateTime;
        $d->setDate( 2000, 01, 01);
        $d->setTime( 0,0,0 );
        # var_dump( $d->format(DateTime::ISO8601) . '' ); 

        like( '/2000-01-01T00:00:00/' , $driver->deflate($d));
    }

}

