<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Query\AlterTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\MySQL\Syntax\IndexHint;

class IndexHintTest extends QueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver() { return new MySQLDriver; }

    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testIndexHintIncompleteSettings()
    {
        $hint = new IndexHint(NULL);
        $this->assertSql('', $hint);
    }


    /**
     * @expectedException BadMethodCallException
     */
    public function testIndexHintBadMethodCallException()
    {
        $hint = new IndexHint(NULL);
        $hint->foo();
    }

}
