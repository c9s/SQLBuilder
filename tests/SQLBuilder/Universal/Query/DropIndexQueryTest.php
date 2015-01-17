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

    public function testDropIndexSimple()
    {
        $q = new DropIndexQuery;
        $q->drop('idx_book')->on('books');
        $this->assertSqlStrings($q, [
            [ new MySQLDriver , "DROP INDEX `idx_book` ON `books`"],
            [ new PgSQLDriver , 'DROP INDEX "idx_book"'],
        ]);
    }

    public function testDropIndexCascade()
    {
        $q = new DropIndexQuery;
        $q->drop('idx_book')->on('books')->ifExists();
        $q->lock('DEFAULT');
        $q->cascade();
        $this->assertSqlStrings($q, [
            [ new MySQLDriver , "DROP INDEX `idx_book` IF EXISTS ON `books` LOCK = DEFAULT"],
            [ new PgSQLDriver , 'DROP INDEX "idx_book" IF EXISTS CASCADE'],
        ]);
    }

    public function testDropIndexRestrict()
    {
        $q = new DropIndexQuery;
        $q->drop('idx_book')->on('books')->ifExists();
        $q->restrict();
        $this->assertSqlStrings($q, [
            [ new MySQLDriver , "DROP INDEX `idx_book` IF EXISTS ON `books`"],
            [ new PgSQLDriver , 'DROP INDEX "idx_book" IF EXISTS RESTRICT'],
        ]);
    }
}

