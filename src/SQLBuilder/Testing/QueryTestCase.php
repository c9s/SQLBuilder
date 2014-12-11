<?php
namespace SQLBuilder\Testing;
use PHPUnit_Framework_TestCase;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

abstract class QueryTestCase extends PHPUnit_Framework_TestCase
{
    public function assertSql($expectedSql, ToSqlInterface $query) 
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $sql = $query->toSql($driver, $args);
        $this->assertSame($expectedSql, $sql);
    }
}



