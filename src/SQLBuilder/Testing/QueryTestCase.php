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

    public function setUp() {
        $this->currentDriver = new MySQLDriver;
        $this->args = new ArgumentArray;
    }

    public function assertSql($expectedSql, ToSqlInterface $query) 
    {
        $sql = $query->toSql($this->currentDriver, $this->args);
        $this->assertSame($expectedSql, $sql);
    }
}



