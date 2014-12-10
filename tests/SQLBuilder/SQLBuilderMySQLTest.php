<?php
use SQLBuilder\QueryBuilder;
use SQLBuilder\Driver\MySQLDriver;

class SQLQueryBuilderMySQLTest extends PHPUnit_PDO_TestCase
{

    public $envVariablePrefix = 'MYSQL_';

    public $schema = array( 'tests/schema/member_mysql.sql' );

    public function getDriver()
    {
        $d = new MySQLDriver;
        $d->setNamedParamMarker();
        return $d;
    }
}
