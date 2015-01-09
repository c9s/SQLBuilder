<?php
namespace SQLBuilder\Testing;
use PHPUnit_Framework_TestCase;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

abstract class QueryTestCase extends PHPUnit_Framework_TestCase
{
    public $currentDriver;

    public $args;

    abstract public function createDriver();

    public function setUp() {
        $this->currentDriver = $this->createDriver();
        $this->args = new ArgumentArray;
    }

    public function getCurrentDriver() {
        return $this->currentDriver;
    }

    public function assertSql($expectedSql, ToSqlInterface $query, BaseDriver $driver = NULL, ArgumentArray $args = NULL) 
    {
        $sql = $query->toSql($driver ?: $this->currentDriver ?: $this->createDriver(), $args ?: $this->args ?: new ArgumentArray);
        $this->assertSame($expectedSql, $sql);
    }
}



