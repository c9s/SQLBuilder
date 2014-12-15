<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Driver\MySQLDriver;

class CreateTableQueryTest extends QueryTestCase
{

    public function createDriver()
    {
        return new MySQLDriver;
    }


    public function testCreateTableQuery()
    {
        $q = new CreateTableQuery('users');
        $q->table('authors');

        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();

        $q->column('first_name')->varchar(32);
        $q->column('last_name')->varchar(16);
        $q->column('age')->tinyint(3);
        $q->column('remark')->text();

        ok($q);

        $this->assertSql('CREATE TABLE `authors`(
`id` integer PRIMARY KEY AUTO_INCREMENT,
`first_name` varchar(32),
`last_name` varchar(16),
`age` tinyint(3),
`remark` text
)', $q);


    }
}

