<?php
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PDODriverFactory;
use SQLBuilder\ParamMarker;
use SQLBuilder\Raw;
use SQLBuilder\Testing\PDOQueryTestCase;

class PDODriverFactoryTest extends PDOQueryTestCase
{

    public function createDriver()
    {
        return MySQLDriver;
    }

    public function driverTypeProvider()
    {
        return [
            ['mysql'],
            //array('pgsql'),
            ['sqlite'],
        ];
    }

    /**
     * @dataProvider driverTypeProvider
     */
    public function testDriverFactory($type)
    {
        $conn = $this->createConnection($type);
        $this->assertNotNull($conn);
        $driver = PDODriverFactory::create($conn);
        $this->assertNotNull($driver);
        $quoted = $driver->quote('foo');
        $this->assertEquals('\'foo\'', $quoted);
    }

    public function testDeflateScalar()
    {
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
    public function testUnknownType()
    {
        $driver = new MySQLDriver;
        $driver->deflateScalar(new Unknown);
    }

    public function testQuoteTable()
    {
        $driver = new MySQLDriver;
        $driver->setQuoteTable(true);
        $this->assertEquals('`users`', $driver->quoteTable('users'));
    }


    public function testAllocateBind()
    {
        $driver = new MySQLDriver;

        $bind = $driver->allocateBind(10);
        $this->assertEquals('p1', $bind->getName());
        $this->assertEquals(':p1', $bind->getMarker());
        $this->assertEquals(10, $bind->getValue());

        $bind = $driver->allocateBind('str');
        $this->assertEquals('p2', $bind->getName());
        $this->assertEquals(':p2', $bind->getMarker());
        $this->assertEquals('str', $bind->getValue());
    }

    public function testAlwaysBindWithBind()
    {
        $args   = new ArgumentArray;
        $driver = new MySQLDriver;
        $driver->alwaysBindValues(true);
        $this->assertEquals(':name', $driver->deflate(new Bind('name', 'Ollie'), $args));
        $bind = $args->getBindingByIndex(0);
        $this->assertEquals('Ollie', $bind->getValue());
    }

    public function testAlwaysBindWithScalar()
    {
        $args   = new ArgumentArray;
        $driver = new MySQLDriver;
        $driver->alwaysBindValues(true);
        $this->assertEquals(':p1', $driver->deflate(10, $args));
        $bind = $args->getBindingByIndex(0);
        $this->assertEquals(10, $bind->getValue());
    }

    public function testAlwaysBindWithRaw()
    {
        $args   = new ArgumentArray;
        $driver = new MySQLDriver;
        $driver->alwaysBindValues(true);
        $this->assertEquals('10', $driver->deflate(new Raw('10'), $args));
    }

    public function testAlwaysBindWithParamMarker()
    {
        $args   = new ArgumentArray;
        $driver = new MySQLDriver;
        $driver->alwaysBindValues(true);
        $this->assertEquals('?', $driver->deflate(new ParamMarker('hack'), $args));
    }

    public function testSetQuoter()
    {
        $conn   = $this->createConnection('mysql');
        $driver = new MySQLDriver;
        $driver->setQuoter(function ($str) use ($conn) {
            return $conn->quote($str);
        });
        $this->assertEquals("'str'", $driver->quote('str'));
    }


}

