<?php
use SQLBuilder\Inflator;

class InflatorTest extends PHPUnit_Framework_TestCase
{
    function testBool()
    {
        $driver = new SQLBuilder\Driver;
        $inf = new Inflator($driver);
        is( 'TRUE', $inf->inflate( true ) );
        is( 'FALSE', $inf->inflate( false ) );
    }

    function testNumber()
    {
        $driver = new SQLBuilder\Driver;
        $inf = new Inflator($driver);

        is( 1 , $inf->inflate( 1 ) );
        is( 1.2 , $inf->inflate( 1.2 ) );
        is( '\'1\'' , $inf->inflate( '1' ) );
        is( 'NULL' , $inf->inflate( null ) );

        $d = new DateTime;
        $d->setDate( 2000, 01, 01);
        $d->setTime( 0,0,0 );
        # var_dump( $d->format(DateTime::ISO8601) . '' ); 

        like( '/2000-01-01T00:00:00/' , $inf->inflate( $d ) );
    }

}

