<?php
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\AlterTableQuery;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Syntax\Column;

class AlterTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver()
    {
        return new MySQLDriver;
    }

    public function setUp()
    {
        parent::setUp();
        $this->testCreateTables();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanUpTables();
    }

    public function cleanUpTables()
    {
        foreach (['products', 'users', 'products_new'] as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertDriverQuery(new MySQLDriver, $dropQuery);
//            $this->assertDriverQuery(new PgSQLDriver, $dropQuery);
            $this->assertDriverQuery(new SQLiteDriver, $dropQuery);
        }
    }

    public function testCreateTables()
    {
        $this->cleanUpTables();

        $createProductTable = new CreateTableQuery('products');
        $createProductTable->column('id')->integer()
            ->primary()
            ->autoIncrement();

        $createProductTable->column('name')->varchar(32);

        $createProductTable->column('description')->text();

        $createProductTable->column('created_by')->int();

        $createProductTable->column('updated_by')->int();

        $this->assertDriverQuery(new MySQLDriver, $createProductTable);
        //$this->assertDriverQuery(new PgSQLDriver, $createProductTable);

        $createUserTable = new CreateTableQuery('users');
        $createUserTable->column('id')->integer()
            ->primary()
            ->autoIncrement();

        $this->assertDriverQuery(new MySQLDriver, $createUserTable);
        //$this->assertDriverQuery(new PgSQLDriver, $createUserTable);
        $this->assertDriverQuery(new SQLiteDriver, $createUserTable);
    }

    public function testDropColumnByName()
    {
        $q = new AlterTableQuery('products');
        $this->assertNotNull($q);
        $q->dropColumnByName('name');
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` DROP COLUMN `name`'],
        ]);
    }

    public function testSyntaxExtenderForAlterColumnDropDefault()
    {
        $q = new AlterTableQuery('products');
        $this->assertNotNull($q);
        $q->registerClass('alterColumn', 'SQLBuilder\MySQL\Syntax\AlterTableAlterColumn');
        $q->alterColumn('name')->dropDefault();

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ALTER COLUMN `name` DROP DEFAULT']
        ]);
    }

    public function testSyntaxExtenderForAlterColumnSetDefault()
    {
        $q = new AlterTableQuery('products');
        $this->assertNotNull($q);
        $q->registerClass('alterColumn', 'SQLBuilder\MySQL\Syntax\AlterTableAlterColumn');
        $q->alterColumn('name')->setDefault('skateboard');

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ALTER COLUMN `name` SET DEFAULT \'skateboard\''],
        ]);
    }

    public function testSyntaxExtenderForSetEngine()
    {
        $q = new AlterTableQuery('products');
        $this->assertNotNull($q);
        $q->registerClass('setEngine', 'SQLBuilder\MySQL\Syntax\AlterTableSetEngine');
        $q->setEngine('InnoDB');
        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ENGINE = \'InnoDB\''],
        ]);
    }


    public function testSyntaxExtenderForSetAutoIncrement()
    {
        $q = new AlterTableQuery('products');
        $this->assertNotNull($q);
        $q->registerClass('setAutoIncrement', 'SQLBuilder\MySQL\Syntax\AlterTableSetAutoIncrement');
        $q->setAutoIncrement(100);
        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` AUTO_INCREMENT = 100'],
        ]);
    }

    public function testModifyColumnNullAttribute()
    {
        // column type is required for MySQL to modify
        $column = new Column('name', 'text');
        $column->null();

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` MODIFY COLUMN `name` text NULL'],
        ]);
    }

    public function testModifyColumnNullAttributePg()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $column = new Column('name');
        $column->null();

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new PgSQLDriver, 'ALTER TABLE "products" ALTER COLUMN "name" DROP NOT NULL'],
        ]);
    }

    public function testModifyColumnNotNullAttributePg()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $column = new Column('name');
        $column->notNull();

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new PgSQLDriver, 'ALTER TABLE "products" ALTER COLUMN "name" SET NOT NULL'],
        ]);
    }

    public function testModifyColumnTypeAttributePg()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $column = new Column('name', 'text');

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new PgSQLDriver, 'ALTER TABLE "products" ALTER COLUMN "name" TYPE text'],
        ]);
    }

    public function testModifyColumnDefaultPg()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $column = new Column('name');
        $column->default('Steve Jobs');

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new PgSQLDriver, 'ALTER TABLE "products" ALTER COLUMN "name" SET DEFAULT \'Steve Jobs\''],
        ]);
    }

    public function testDropColumn()
    {
        $q = new AlterTableQuery('products');
        $q->dropColumn(new Column('name'));

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` DROP COLUMN `name`'],
        ]);
    }

    public function testDropPrimaryKey()
    {
        $q = new AlterTableQuery('products');
        $q->dropPrimaryKey();

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` DROP PRIMARY KEY'],
        ]);
    }

    public function testOrderByColumns()
    {
        $q = new AlterTableQuery('products');
        $q->orderBy(['name', 'description', 'created_on', 'created_by']);

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ORDER BY `name`,`description`,`created_on`,`created_by`'],
        ]);
    }


    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testModifyColumnPgWithoutChanges()
    {
        $this->markTestSkipped(
            'The PostgreSQL extension is not available.'
        );

        $column = new Column('name');
        $q      = new AlterTableQuery('products');
        $q->modifyColumn($column);
        $this->assertSqlStrings($q, [
            [new PgSQLDriver, 'ALTER TABLE "products" ALTER COLUMN "name" SET DEFAULT \'Steve Jobs\''],
        ]);
    }


    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testModifyColumnWithIncompleteSettings()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;
        $column = new Column('name');
        $q      = new AlterTableQuery('products');
        $q->modifyColumn($column);
        $sql = $q->toSql($driver, $args);
    }

    /**
     * @expectedException SQLBuilder\Exception\UnsupportedDriverException
     */
    public function testModifyColumnSqliteUnsupported()
    {
        $column = new Column('name', 'varchar(30)');
        $column->default('John');
        $column->null();

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new SQLiteDriver, $q);
        $this->assertSqlStrings($q, [
            [new SQLiteDriver, 'ALTER TABLE `products` MODIFY COLUMN `name` varchar(30) NULL DEFAULT \'John\''],
        ]);
    }

    public function testModifyColumnDefaultAttribute()
    {
        $column = new Column('name', 'varchar(30)');
        $column->default('John');
        $column->null();

        $q = new AlterTableQuery('products');
        $q->modifyColumn($column);

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` MODIFY COLUMN `name` varchar(30) NULL DEFAULT \'John\''],
        ]);
    }

    public function testAddColumn()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;

        $column = new Column('last_name', 'varchar(30)');
        $column->default('');
        $column->notNull();

        $q = new AlterTableQuery('products');
        $q->addColumn($column);

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ADD COLUMN `last_name` varchar(30) NOT NULL DEFAULT \'\''],
        ]);
    }

    public function testAddColumnAfterName()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;

        $column = new Column('last_name', 'varchar(30)');
        $column->default('');
        $column->notNull();

        $q = new AlterTableQuery('products');
        $q->addColumn($column)->after('name');

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ADD COLUMN `last_name` varchar(30) NOT NULL DEFAULT \'\' AFTER `name`'],
        ]);
    }

    public function testAddColumnFirst()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;

        $column = new Column('last_name', 'varchar(30)');
        $column->default('');
        $column->notNull();

        $q = new AlterTableQuery('products');
        $q->addColumn($column)->first();

        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` ADD COLUMN `last_name` varchar(30) NOT NULL DEFAULT \'\' FIRST'],
        ]);
    }

    public function testRenameTable()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;
        $q      = new AlterTableQuery('products');
        $q->rename('products_new');
        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        $this->assertEquals('ALTER TABLE `products` RENAME TO `products_new`', $sql);


        $q = new AlterTableQuery('products_new');
        $q->rename('products');
        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        $this->assertEquals('ALTER TABLE `products_new` RENAME TO `products`', $sql);
    }

    public function testAddForeignKey()
    {
        $q = new AlterTableQuery('products');
        $q->add()->foreignKey('created_by')
            ->references('users', ['id']);

        $q->add()->constraint('fk_updated_by')->foreignKey('updated_by')
            ->references('users', ['id']);
        $this->assertDriverQuery(new MySQLDriver, $q);


        //$this->assertDriverQuery(new PgSQLDriver, $q);

        $this->assertSqlStrings($q, [
            [
                new MySQLDriver,
                'ALTER TABLE `products` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),' . "\n" . '  ADD CONSTRAINT `fk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)'
            ],
        ]);
    }

    /**
     * @expectedException SQLBuilder\Exception\UnsupportedDriverException
     */
    public function testRenameColumnSqlite()
    {
        $q = new AlterTableQuery('products');
        $q->renameColumn(new Column('name'), new Column('title', 'varchar(30)'));
        $this->assertDriverQuery(new SQLiteDriver, $q);
    }

    public function testRenameColumnFromColumnObject()
    {
        $q = new AlterTableQuery('products');
        $q->renameColumn(new Column('name'), new Column('title', 'varchar(30)'));

        $this->assertDriverQuery(new MySQLDriver, $q);
        //$this->assertDriverQuery(new PgSQLDriver, $q);

        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)'],
            //[new PgSQLDriver, 'ALTER TABLE "products" RENAME COLUMN "name" TO "title"'],
        ]);
    }


    public function testChangeColumn()
    {
        $q = new AlterTableQuery('products');
        $q->changeColumn('name', new Column('title', 'varchar(30)'));
        $this->assertDriverQuery(new MySQLDriver, $q);
        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)'],
        ]);
    }


    public function testRenameColumn()
    {
        $q = new AlterTableQuery('products');
        $q->renameColumn('name', new Column('title', 'varchar(30)'));

        $this->assertDriverQuery(new MySQLDriver, $q);
        //$this->assertDriverQuery(new PgSQLDriver, $q);

        $this->assertSqlStrings($q, [
            [new MySQLDriver, 'ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)'],
            //[new PgSQLDriver, 'ALTER TABLE "products" RENAME COLUMN "name" TO "title"'],
        ]);
    }

    public function testRenameColumnFromColumnClass()
    {
        $driver = new MySQLDriver;
        $args   = new ArgumentArray;
        $q      = new AlterTableQuery('products');
        $q->renameColumn(new Column('name'), new Column('title', 'varchar(30)'));

        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        $this->assertEquals('ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)', $sql);
    }
}
