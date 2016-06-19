<?php
use SQLBuilder\Inflator;

class InflatorTest extends PHPUnit_Framework_TestCase
{
    function testBool()
    {
        $args = new SQLBuilder\ArgumentArray;
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $this->assertEquals( 'TRUE', $driver->deflate( true , $args) );
        $this->assertEquals( 'FALSE', $driver->deflate( false , $args) );
    }

    function testNumber()
    {
        $driver = new SQLBuilder\Driver\MySQLDriver;
        $this->assertEquals( 1 , $driver->deflate( 1 ) );
        $this->assertEquals( 1.2 , $driver->deflate( 1.2 ) );
        $this->assertEquals( '\'1\'' , $driver->deflate( '1' ) );
        $this->assertEquals( 'NULL' , $driver->deflate( null ) );

        $d = new DateTime;
        $d->setDate( 2000, 01, 01);
        $d->setTime( 0,0,0 );
        # var_dump( $d->format(DateTime::ISO8601) . '' ); 
        $this->assertContains('2000-01-01 00:00:00', $driver->deflate($d));
    }

}

