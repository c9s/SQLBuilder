<?php
namespace SQLBuilder\Testing;
use PHPUnit_Framework_TestCase;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

/**
 * @codeCoverageIgnore
 */
abstract class QueryTestCase extends PHPUnit_Framework_TestCase
{
    public $currentDriver;

    public $args;

    public function createDriver() { 
        // XXX:
    }

    public function setUp() {
        $this->currentDriver = $this->createDriver();
        $this->args = new ArgumentArray;
    }

    public function getCurrentDriver() {
        return $this->currentDriver;
    }

    public function assertSql($expectedSql, ToSqlInterface $query, BaseDriver $driver = NULL, ArgumentArray $args = NULL) 
    {
        $sql = $query->toSql($driver ?: $this->currentDriver ?: $this->createDriver(), $args ?: $this->args ?: new ArgumentArray);
        $this->assertSame($expectedSql, $sql);
    }

    public function assertRequirements(ToSqlInterface $query, array $defines) {
        foreach($defines as $define) {
            list($driver, $expectedSQL) = $define;
            $args = new ArgumentArray;
            $sql = $query->toSql($driver, $args);
            is($expectedSQL, $sql);
        }
    }

}



