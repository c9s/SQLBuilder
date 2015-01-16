<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\DropIndexQuery;
use SQLBuilder\Testing\QueryTestCase;


class DropIndexQueryTest extends QueryTestCase
{

    public function createDriver() {
        return new MySQLDriver; 
    }

    public function testDropIndex()
    {
        $q = new DropIndexQuery;
        $q->drop('idx_book')->on('books')->ifExists();

        $this->assertSqlStatements($q, [
            [ new MySQLDriver , "DROP INDEX `idx_book` IF EXISTS ON `books`"],
            [ new PgSQLDriver , 'DROP INDEX "idx_book" IF EXISTS'],
        ]);

    }
}

