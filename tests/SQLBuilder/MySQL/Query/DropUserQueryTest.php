<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\MySQL\Query\DropUserQuery;

class DropUserQueryTest extends PHPUnit_Framework_TestCase
{
    public function testDropSingleUser()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new DropUserQuery;
        $q->user()->account('monty')->host('localhost');
        $sql = $q->toSql($driver, $args);
        $this->assertEquals("DROP USER `monty`@`localhost`", $sql);
    }

    public function testDropSingleUserWithSpecString()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new DropUserQuery;
        $q->user('monty@localhost');
        $sql = $q->toSql($driver, $args);
        $this->assertEquals("DROP USER `monty`@`localhost`", $sql);
    }
}

