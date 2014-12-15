<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;

class MySQLCreateTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';
    // public $schema = array( 'tests/schema/member_mysql.sql' );

    public function createDriver() {
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

        $this->query('DROP TABLE IF EXISTS `authors`');
        $this->assertQuery($q);

        $this->assertSql('CREATE TABLE `authors`(
`id` integer PRIMARY KEY AUTO_INCREMENT,
`first_name` varchar(32),
`last_name` varchar(16),
`age` tinyint(3),
`remark` text
)', $q);


        $dropQuery = new DropTableQuery('authors');
        $dropQuery->IfExists();
        $this->assertSql('DROP TABLE IF EXISTS `authors`', $dropQuery);
        $this->assertQuery($dropQuery);

    }
}

