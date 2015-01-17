<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\CreateDatabaseQuery;
use SQLBuilder\Universal\Query\DropDatabaseQuery;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Testing\PDOQueryTestCase;

class CreateDatabaseQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver() {
        return new MySQLDriver;
    }

    public function testQuery() {
        $q = new DropDatabaseQuery('test123123');
        $q->ifExists();
        $this->assertQuery($q);


        $q = new CreateDatabaseQuery('test123123');
        $q->characterSet('utf8');
        $this->assertSql("CREATE DATABASE `test123123` CHARACTER SET 'utf8'", $q);
        $this->assertQuery($q);

        $q = new DropDatabaseQuery('test123123');
        $this->assertSql("DROP DATABASE `test123123`", $q);
        $this->assertQuery($q);
    }

    public function testCreateDatabaseQuery() {
        $q = new CreateDatabaseQuery;
        $q->create('test')
            ->characterSet('utf8')
            ->collate('en_US.UTF-8');
        $this->assertSqlStatements($q, [ 
            [ new PgSQLDriver, 'CREATE DATABASE "test" LC_COLLATE \'en_US.UTF-8\''],
            [ new MySQLDriver, "CREATE DATABASE `test` CHARACTER SET 'utf8' COLLATE 'en_US.UTF-8'"],
        ]);
    }


}

