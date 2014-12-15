<?php
use SQLBuilder\Universal\Query\CreateTableQuery;

class CreateTableQueryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTableQuery()
    {
        $q = new CreateTableQuery('users');
        $q->table('authors');
        $q->column('first_name')->varchar(32);
        $q->column('last_name')->varchar(16);
        $q->column('age')->tinyint(3);
        ok($q);
    }
}

