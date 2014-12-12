<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\CreateDatabaseQuery;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Testing\QueryTestCase;

class CreateDatabaseQueryTest extends QueryTestCase
{
    public function createDriver() {
        return new MySQLDriver;
    }

    public function testQuery() {
        $q = new CreateDatabaseQuery;
        ok($q);
        $q->create('test')->characterSet('utf8');
        $this->assertSql("CREATE DATABASE `test` CHARACTER SET 'utf8'", $q);
    }

    public function testPgQuery() {
        $driver = new PgSQLDriver;
        $q = new CreateDatabaseQuery;
        ok($q);
        $q->create('test')
            ->characterSet('utf8');
        $q->collate('en_US.UTF-8');
        $this->assertSql('CREATE DATABASE "test" LC_COLLATE \'en_US.UTF-8\'', $q, $driver);
    }


}

