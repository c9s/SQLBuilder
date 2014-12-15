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
        $q->column('age')->tinyint(3)->unsigned()->null();
        $q->column('phone')->varchar(24)->null();
        $q->column('email')->varchar(128)->notNull();
        $q->column('confirmed')->boolean()->default(false);
        $q->column('remark')->text();

        ok($q);

        $dropQuery = new DropTableQuery('authors');
        $dropQuery->IfExists();
        $this->assertSql('DROP TABLE IF EXISTS `authors`', $dropQuery);
        $this->assertQuery($dropQuery);
        $this->assertQuery($q);

        $this->assertSql('CREATE TABLE `authors`(
`id` integer PRIMARY KEY AUTO_INCREMENT,
`first_name` varchar(32),
`last_name` varchar(16),
`age` tinyint(3) UNSIGNED NULL,
`phone` varchar(24) NULL,
`email` varchar(128) NOT NULL,
`confirmed` boolean DEFAULT FALSE,
`remark` text
)', $q);


        $this->assertQuery($dropQuery);

    }
}

