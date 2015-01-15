<?php
use SQLBuilder\Driver\PDODriverFactory;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\DataType\Unknown;

class PDODriverFactoryTest extends PDOQueryTestCase
{

    public function createDriver() {
        return MySQLDriver;
    }

    public function driverTypeProvider() 
    {
        return array(
            array('mysql'),
            array('pgsql'),
            array('sqlite'),
        );
    }


    /**
     * @dataProvider driverTypeProvider
     */
    public function testDriverFactory($type)
    {
        $conn = $this->createConnection($type);
        ok($conn);
        $driver = PDODriverFactory::create($conn);
        ok($driver);

        $quoted = $driver->quote('foo');
        is('\'foo\'', $quoted);
    }

    public function testDeflateScalar() {
        $driver = new MySQLDriver;
        $this->assertSame('0', $driver->deflateScalar(0));
        $this->assertSame('1.23', $driver->deflateScalar(1.23));
        $this->assertSame('TRUE', $driver->deflateScalar(true));
        $this->assertSame('FALSE', $driver->deflateScalar(false));
        $this->assertSame('NULL', $driver->deflateScalar(null));
        $this->assertSame("'string'", $driver->deflateScalar('string'));
    }


    /**
     * @expectedException Exception
     */
    public function testUnknownType() {
        $driver = new MySQLDriver;
        $driver->deflateScalar(new Unknown);
    }


    public function testAllocateBind() {
        $driver = new MySQLDriver;

        $bind = $driver->allocateBind(10);
        is('p1', $bind->getName());
        is(':p1', $bind->getMarker());
        is(10, $bind->getValue());

        $bind = $driver->allocateBind('str');
        is('p2', $bind->getName());
        is(':p2', $bind->getMarker());
        is('str', $bind->getValue());
    }



}

