<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\MySQLQuery\CreateUserQuery;

class CreateUserQueryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateSingleUser()
    {
        /**
         * CREATE USER 'monty'@'localhost' IDENTIFIED BY 'some_pass';
         */
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedBy('some_pass');
        $sql = $q->toSql($driver, $args);
        is("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass'", $sql);
    }



    public function testCreateSingleUserWithAuthPlugin()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedWith('mysql_native_password');
        $sql = $q->toSql($driver, $args);
        is("CREATE USER `monty`@`localhost` IDENTIFIED WITH `mysql_native_password`", $sql);
    }

    public function testCreateMultipleUser()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedBy('some_pass');
        $q->user()->account('john')->host('%')->identifiedBy('some_pass');
        $sql = $q->toSql($driver, $args);
        is("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass', `john`@`%` IDENTIFIED BY 'some_pass'", $sql);
    }
}

