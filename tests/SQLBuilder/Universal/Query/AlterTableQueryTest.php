<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Query\AlterTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\ArgumentArray;

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
        foreach(array('products','users') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
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
        // is('', $sql);
    }
}

