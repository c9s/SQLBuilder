<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Raw;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;

class MySQLCreateTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver()
    {
        return new MySQLDriver;
    }

    public function setUp()
    {
        parent::setUp();

        // Clean up
        foreach (['users', 'groups', 'authors', 'points', 'ts_tests'] as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function tearDown()
    {
        foreach (['users', 'groups', 'authors', 'points'] as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function testCreateTableWithForeignKeyConstraint()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer()->primary();
        $this->assertQuery($q);

        $q = new CreateTableQuery('users');
        $q->column('group_id')->int();
        $q->foreignKey(['group_id'])
            ->references('groups', 'id')
            ->onDelete('CASCADE')
            ->onUpdate('CASCADE');
        $this->assertSql('CREATE TABLE `users`(
`group_id` int,
FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)', $q);
        $this->assertQuery($q);
    }


    public function testChangingColumnName()
    {
        $q = new CreateTableQuery('points');
        $q->column('x')->float(10, 2)->name('x2');
        $this->assertSqlStrings($q, [
            [
                new MySQLDriver,
                'CREATE TABLE `points`(
`x2` float(10,2)
)'
            ],
        ]);
    }

    public function testCreateTableWithDecimalsAndLength()
    {
        $q = new CreateTableQuery('points');
        $q->column('x')->float(10, 2);
        $q->column('y')->float(10, 2);
        $q->column('z')->float(10, 2);
        $q->column('strength')->double(10, 2);
        $this->assertSqlStrings($q, [
            [
                new MySQLDriver,
                'CREATE TABLE `points`(
`x` float(10,2),
`y` float(10,2),
`z` float(10,2),
`strength` double(10,2)
)'
            ],
        ]);
        $this->assertQuery($q);

        $dropQuery = new DropTableQuery('points');
        $dropQuery->IfExists();
        $this->assertQuery($dropQuery);
    }

    public function testTimestampColumns()
    {
        $a = 1;
        $q = new CreateTableQuery('ts_tests');
        $q->column('c' . $a++)->timestamp()->null()->timezone(); // timezone is for pgsql
        $q->column('c' . $a++)->timestamp()->null();

        // MySQL 5.7 requires the last timestamp column to have default current_timestamp
        $q->column('c' . $a++)->timestamp()->default(new Raw('CURRENT_TIMESTAMP'));
        $this->assertQuery($q);
    }

    public function testTimestampOnUpdate()
    {
        $a = 1;
        $q = new CreateTableQuery('ts_tests');
        // MySQL 5.7 requires the last timestamp column to have default current_timestamp
        $q->column('c' . $a++)->timestamp()
            ->default(new Raw('CURRENT_TIMESTAMP'))
            ->onUpdate(new Raw('CURRENT_TIMESTAMP'));
        $this->assertQuery($q);
    }

    public function testColumns()
    {
        $a = 1;
        $q = new CreateTableQuery('groups');
        $q->column('c' . $a++)->int();
        $q->column('c' . $a++)->integer();
        $q->column('c' . $a++)->tinyInt();
        $q->column('c' . $a++)->smallInt();
        $q->column('c' . $a++)->mediumInt();
        $q->column('c' . $a++)->bigInt();


        $q->column('c' . $a++)->int()->length(6);
        $q->column('c' . $a++)->int(3);
        $q->column('c' . $a++)->int(3)->default(3);
        $q->column('c' . $a++)->integer(3);
        $q->column('c' . $a++)->tinyInt(3);
        $q->column('c' . $a++)->smallInt(3);
        $q->column('c' . $a++)->mediumInt(3);
        $q->column('c' . $a++)->bigInt(3);

        $q->column('c' . $a++)->int(3)->unsigned();
        $q->column('c' . $a++)->real(6, 1)->decimals(2);

        $q->column('c' . $a++)->tinyblob();
        $q->column('c' . $a++)->blob();
        $q->column('c' . $a++)->mediumblob();
        $q->column('c' . $a++)->longblob();

        $q->column('c' . $a++)->char(12);
        $q->column('c' . $a++)->varchar(12);
        $q->column('c' . $a++)->varchar(12)->unique();
        $q->column('c' . $a++)->text();
        $q->column('c' . $a++)->mediumText();
        $q->column('c' . $a++)->longText();
        $q->column('c' . $a++)->binary();
        $q->column('c' . $a++)->binary(255);


        $q->column('c' . $a++)->bool();
        $q->column('c' . $a++)->boolean();
        $q->column('c' . $a++)->enum(['a', 'b', 'c']);

        $q->column('c' . $a++)->date();
        $q->column('c' . $a++)->time();
        $q->column('c' . $a++)->time()->default(function ($column, $driver) {
            return $driver->deflate('02:00');
        });
        $q->column('c' . $a++)->year();


        $q->column('c' . $a++)->datetime();

        $q->column('c' . $a++)->decimal();
        $q->column('c' . $a++)->decimal(5);

        $q->column('c' . $a++)->numeric();
        $q->column('c' . $a++)->numeric(10)->comment('FOR NUMERIC');

        $q->column('c' . $a++)->bit(3);
        $this->assertQuery($q);
    }


    public function testPgCreateTable()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $a = 1;
        $q = new CreateTableQuery('groups');
        $q->column('c' . $a++)->serial();
        $q->column('c' . $a++)->int();
        $q->column('c' . $a++)->int()->unsigned();
        $q->column('c' . $a++)->int(1);
        $q->column('c' . $a++)->double(2, 1);
        $this->assertDriverQuery(new PgSQLDriver, $q);
    }


    public function testCreateTableWithSimpleIndex()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer();
        $q->column('name')->varchar(20);
        $q->column('content')->text();
        $q->column('blob_content')->blob();
        $q->index(['name'])->name('name_idx')->using('BTREE');

        $this->assertSql('CREATE TABLE `groups`(
`id` int,
`name` varchar(20),
`content` text,
`blob_content` blob,
INDEX `name_idx` USING BTREE (`name`)
)', $q);
        $this->assertQuery($q);
    }

    public function testCreateTableWithPrimaryKey()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer();
        $q->engine('InnoDB');
        $q->primaryKey('id');
        $this->assertQuery($q);
        $this->assertSql('CREATE TABLE `groups`(
`id` int,
PRIMARY KEY (`id`)
) ENGINE=InnoDB', $q);
    }

    public function testCreateTableQuery()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $q->engine('InnoDB');
        $this->assertQuery($q);

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
        $q->column('types')->set('student', 'teacher');
        $q->column('remark')->text();

        $q->column('group_id')->integer();

        // create table t1 (
        //      id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        //      product_id integer unsigned,  constraint `fk_product_id` 
        //      FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) );
        $q->constraint('fk_group_id')
            ->foreignKey('group_id')
            ->references('groups', 'id')
            ->onDelete('CASCADE')
            ->onUpdate('CASCADE');

        $q->uniqueKey('email');
        $q->engine('InnoDB');

        $dropQuery = new DropTableQuery('authors');
        $dropQuery->IfExists();
        $this->assertSql('DROP TABLE IF EXISTS `authors`', $dropQuery);
        $this->assertQuery($dropQuery);
        $this->assertSql('CREATE TABLE `authors`(
`id` int PRIMARY KEY AUTO_INCREMENT,
`first_name` varchar(32),
`last_name` varchar(16),
`age` tinyint(3) UNSIGNED NULL,
`phone` varchar(24) NULL,
`email` varchar(128) NOT NULL,
`confirmed` boolean DEFAULT FALSE,
`types` set(\'student\', \'teacher\'),
`remark` text,
`group_id` int,
CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE KEY (`email`)
) ENGINE=InnoDB', $q);
        $this->assertQuery($q);
        $this->assertQuery($dropQuery); // drop again to test the if exists.
    }
}

