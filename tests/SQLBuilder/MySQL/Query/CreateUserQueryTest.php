<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\Testing\QueryTestCase;

class CreateUserQueryTest extends QueryTestCase
{
    public function createDriver() { 
        return new MySQLDriver;
    }

    public function testCreateSingleUser()
    {
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedBy('some_pass');
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass'", $q);
    }

    public function testCreateUserWithSpecString() {
        $q = new CreateUserQuery;
        $q->user('monty@localhost')->identifiedBy('some_pass');
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass'", $q);
    }

    public function testCreateUserWithSpecString2() {
        $q = new CreateUserQuery;
        $q->user('`monty`@`localhost`')->identifiedBy('some_pass');
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass'", $q);
    }

    public function testCreateUserWithHashPassword() {
        $q = new CreateUserQuery;
        $q->user('`monty`@`localhost`')->identifiedBy('*90E462C37378CED12064BB3388827D2BA3A9B689', true);
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED BY PASSWORD '*90E462C37378CED12064BB3388827D2BA3A9B689'", $q);
    }


    public function testCreateSingleUserWithAuthPlugin()
    {
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedWith('mysql_native_password');
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED WITH `mysql_native_password`", $q);
    }

    public function testCreateMultipleUser()
    {
        $q = new CreateUserQuery;
        $q->user()->account('monty')->host('localhost')->identifiedBy('some_pass');
        $q->user()->account('john')->host('%')->identifiedBy('some_pass');
        $this->assertSql("CREATE USER `monty`@`localhost` IDENTIFIED BY 'some_pass', `john`@`%` IDENTIFIED BY 'some_pass'", $q);
    }
}

