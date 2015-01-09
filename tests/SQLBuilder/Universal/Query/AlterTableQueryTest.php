<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Query\AlterTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\Column;

class AlterTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver() {
        return new MySQLDriver;
    }

    public function setUp()
    {
        parent::setUp();
        $this->tearDown();

        $createProductTable = new CreateTableQuery('products');
        $createProductTable->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $createProductTable->column('name')->varchar(32);
        $createProductTable->column('created_by')->int();
        $createProductTable->column('updated_by')->int();

        $this->assertQuery($createProductTable);

        $createUserTable = new CreateTableQuery('users');
        $createUserTable->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $this->assertQuery($createUserTable);
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach(array('products','users','products_new') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }


    public function testRenameTable()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new AlterTableQuery('products');
        $q->rename('products_new');
        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        is('ALTER TABLE `products` RENAME  TO `products_new`', $sql);


        $q = new AlterTableQuery('products_new');
        $q->rename('products');
        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        is('ALTER TABLE `products_new` RENAME  TO `products`', $sql);
    }


    public function testAddForeignKey()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new AlterTableQuery('products');

        $q->add()->foreignKey('created_by')
            ->references('users', array('id'))
                ;

        $q->add()->foreignKey('updated_by')
            ->references('users', array('id'))
                ;

        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        is('ALTER TABLE `products` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`), ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)', $sql);
    }


    public function testRenameColumn()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new AlterTableQuery('products');
        $q->renameColumn('name', new Column('title', 'varchar(30)'));

        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        is('ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)', $sql);
    }


    public function testRenameColumnFromColumnClass()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $q = new AlterTableQuery('products');
        $q->renameColumn(new Column('name'), new Column('title', 'varchar(30)'));

        $sql = $q->toSql($driver, $args);
        $this->assertQuery($q);
        is('ALTER TABLE `products` CHANGE COLUMN `name` `title` varchar(30)', $sql);
    }




}

