<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\DropDatabaseQuery;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Testing\QueryTestCase;

class DropDatabaseQueryTest extends QueryTestCase
{
    public function createDriver() {
        return new MySQLDriver;
    }

    public function testQuery() {
        $q = new DropDatabaseQuery('test');
        ok($q);
        $this->assertSql("DROP DATABASE `test`", $q);
        $q->drop('test2');
        $this->assertSql("DROP DATABASE `test2`", $q);
    }

    public function testDropDatabaseQuery() {
        $q = new DropDatabaseQuery('test');
        $this->assertSqlStrings($q, [ 
            [ new PgSQLDriver, 'DROP DATABASE "test"'],
            [ new MySQLDriver, "DROP DATABASE `test`"],
        ]);
    }
}

