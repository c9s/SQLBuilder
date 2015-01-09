<?php
use SQLBuilder\Driver\PDODriverFactory;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Testing\PDOQueryTestCase;

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
    }
}

