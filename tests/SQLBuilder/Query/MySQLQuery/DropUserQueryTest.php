<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\MySQLQuery\CreateUserQuery;
use SQLBuilder\Query\MySQLQuery\DropUserQuery;

class DropUserQueryTest extends PHPUnit_Framework_TestCase
{
    public function testDropSingleUser()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new DropUserQuery;
        $q->user()->account('monty')->host('localhost');
        $sql = $q->toSql($driver, $args);
        is("DROP USER `monty`@`localhost`", $sql);
    }

    public function testDropSingleUserWithSpecString()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new DropUserQuery;
        $q->user('monty@localhost');
        $sql = $q->toSql($driver, $args);
        is("DROP USER `monty`@`localhost`", $sql);
    }
}

