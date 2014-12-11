<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\DropIndexQuery;
use SQLBuilder\Testing\QueryTestCase;


class DropIndexQueryTest extends QueryTestCase
{

    public function createDriver() { return new MySQLDriver; }

    public function testDropIndex()
    {
        $q = new DropIndexQuery;
        $q->drop('idx_book')->on('books');
        $this->assertSql("DROP INDEX `idx_book` ON `books`", $q);
    }
}

